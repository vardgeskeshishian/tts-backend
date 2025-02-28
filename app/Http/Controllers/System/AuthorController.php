<?php


namespace App\Http\Controllers\System;

use App\Constants\SubmissionsEnv;
use App\Http\Controllers\Api\ApiController;
use App\Models\Authors\Author;
use App\Models\Authors\AuthorApplicant;
use App\Models\Authors\AuthorProfile;
use App\Models\Authors\AuthorSubmission;
use App\Models\Authors\AuthorSubmissionComment;
use App\Models\Libs\Role;
use App\Models\User;
use App\Services\Authors\AuthorProfileService;
use App\Services\ImagesService;
use App\Services\MetaService;
use App\Services\UserRoleService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Mail;

class AuthorController extends ApiController
{
    public function listView()
    {
        $list = Author::paginate();

        return view('admin.authors.list', compact('list'));
    }

    /**
     * @param Author $author
     *
     * @return View
     */
    public function authorView(Author $author)
    {
        return view('admin.authors.single', compact('author'));
    }

    /**
     * @param Author $author
     * @param AuthorSubmission $authorSubmission
     *
     * @return View
     */
    public function submissionView(Author $author, AuthorSubmission $authorSubmission)
    {
        $submission = $authorSubmission;
        $statuses = SubmissionsEnv::STATUSES;

        return view('admin.authors.submission', compact('author', 'submission', 'statuses'));
    }


    public function allSubmissionsTableView()
    {
        $query = AuthorSubmission::query();

        $filters = request()->get('filters');

        $query->when(isset($filters['final_status']), function ($query) use ($filters) {
            $query->where('final_status', $filters['final_status']);
        });

        $list = $query->orderByDesc('created_at')->paginate(15);

        $reviewerStatuses = SubmissionsEnv::REVIEWER_STATUSES;
        $finalStatuses = SubmissionsEnv::FINAL_STATUSES;
        $statuses = SubmissionsEnv::STATUSES;

        $admins = User::where('role', 'admin')->get();

        return view('admin.authors.submissions', compact('list', 'statuses', 'reviewerStatuses', 'finalStatuses', 'admins', 'filters'));
    }

    /**
     * @param Author $author
     * @param AuthorSubmission $authorSubmission
     *
     * @return RedirectResponse
     */
    public function newComment(Author $author, AuthorSubmission $authorSubmission)
    {
        $type = request()->input('type');
        $isAuthorComment = $type === SubmissionsEnv::COMMENT_TYPE_PUBLIC && rand(0, 1) === 0;

        AuthorSubmissionComment::create([
            'comment' => request()->input('comment'),
            'comment_type' => request()->input('type'),
            'submission_id' => $authorSubmission->id,
            'user_id' => $isAuthorComment ? $author->id : request()->input('reviewer'),
        ]);

        return redirect()->back()->withFragment("submissionForm{$authorSubmission->id}");
    }

    /**
     * @param Author $author
     * @param AuthorSubmission $authorSubmission
     * @param AuthorSubmissionComment $authorSubmissionComment
     *
     * @return RedirectResponse
     */
    public function deleteComment(
        Author                  $author,
        AuthorSubmission        $authorSubmission,
        AuthorSubmissionComment $authorSubmissionComment
    )
    {
        if ($authorSubmissionComment->submission_id !== $authorSubmission->id) {
            return redirect()->back()->withFragment("submissionForm{$authorSubmission->id}");
        }

        if (!$authorSubmissionComment->user->is_admin) {
            return redirect()->back()->withFragment("submissionForm{$authorSubmission->id}");
        }

        $authorSubmissionComment->delete();

        return redirect()->back()->withFragment("submissionForm{$authorSubmission->id}");
    }

    /**
     * @param Author $author
     * @param AuthorSubmission $authorSubmission
     *
     * @return RedirectResponse
     */
    public function changeStatus(Author $author, AuthorSubmission $authorSubmission)
    {
        $isReviewerStatus = request()->has('reviewer_status');
        $isFinalStatus = request()->has('final_status');

        $data = [];

        if ($isFinalStatus) {
            $data['final_status'] = request()->input('final_status');

            $emailData = [];

            $latestComment = optional($authorSubmission->publicComments()->latest()->first())->comment;
            $mailAddons = $latestComment ? ['comment' => $latestComment] : [];

            switch ($data['final_status']) {
                case SubmissionsEnv::STATUS_SOFT_R:
                    $emailData = [
                        'title' => "Regarding your track '{$authorSubmission->track_name}'",
                        'body' => "Your track '{$authorSubmission->track_name}' still needs to be improved",
                        'addons' => $mailAddons,
                    ];

                    break;
                case SubmissionsEnv::STATUS_HARD_R:
                    $emailData = [
                        'title' => "Regarding your track '{$authorSubmission->track_name}'",
                        'body' => "Unfortunately, your track '{$authorSubmission->track_name}' is not approved for the TakeTones music library.",
                        'addons' => $mailAddons,
                    ];

                    break;
                case SubmissionsEnv::STATUS_APPROVED:
                    $emailData = [
                        'title' => "Regarding your track '{$authorSubmission->track_name}'",
                        'body' => "Congratulations! your track '{$authorSubmission->track_name}' was successfully verified. Now you need to send us all versions of your track, as well as fill in the missing description fields here https://taketones.com/profile/submit-music",
                        'addons' => [
                        ]
                    ];

                    break;
                default:
                    break;
            }

            if (!empty($emailData)) {
                $this->dispatch(function () use ($author, $emailData) {
                    Mail::send('email.general-email', $emailData, function ($message) use ($author, $emailData) {
                        $message->from('no-reply@taketones.com')->to($author->email)->subject($emailData['title']);
                    });
                });
            }
        }

        if ($isReviewerStatus) {
            $data['reviewer_status'] = request()->input('reviewer_status');
        }

        $authorSubmission->fill($data)->save();

        return redirect()->back()->withFragment("submissionForm{$authorSubmission->id}");
    }

    public function viewCreateProfile()
    {
        $userId = request()->input('user_id');

        return view('admin.authors.view.create-profile', compact('userId'));
    }

    public function viewEditAuthorProfile(AuthorProfile $profile)
    {
        return view('admin.authors.view.edit-profile', compact('profile'));
    }

    public function apiLinkProfiles()
    {
        $userId = request()->input('user_id');
        $user = User::find($userId);

        if (!$user->isAuthor()) {
            $changeRoleService = app(UserRoleService::class);
            $changeRoleService->assignRoleToUser($user, Role::ROLE_AUTHOR);
        }

        $profiles = request()->input('profiles');

        if (!empty($profiles)) {
            $existingProfiles = AuthorProfile::where('user_id', $userId)->pluck('id')->values();

            $diff = $existingProfiles->diff($profiles);

            AuthorProfile::whereIn('id', [$profiles])->update(['user_id' => $userId]);

            if ($diff->isNotEmpty()) {
                AuthorProfile::whereIn('id', [$diff])->update(['user_id' => null]);
            }
        }

        return redirect()->route('get-users-userid-profile', ['userId' => $user->id]);
    }

    public function apiCreateProfile(AuthorProfileService $profileService)
    {
        $userId = request()->input('user_id');
        $user = User::find($userId);

        if (!$user->isAuthor()) {
            $changeRoleService = app(UserRoleService::class);
            $changeRoleService->assignRoleToUser($user, Role::ROLE_AUTHOR);
        }

        $profileService->createNewProfile(Author::find($userId));

        return redirect()->route('get-users-userid-profile', ['userId' => $user->id]);
    }

    public function apiEditAuthorProfile(MetaService $metaService, ImagesService $service, AuthorProfile $profile)
    {
        $profile->fill(request()->except('images', 'meta'))->save();
        $metaService->fillInForObject($profile, request()->input('meta'));
        $service->upload($profile, request()->files->get('images'));

        return redirect()->back();
    }

    public function viewApplicants()
    {
        $list = AuthorApplicant::orderByDesc('created_at')->paginate();

        return view('admin.authors.view.applicants', compact('list'));
    }

    public function apiApplicationChangeStatus(AuthorApplicant $applicant)
    {
        $status = request()->input('status');

        $applicant->declined = $status === 'declined' ? 1 : 0;
        $applicant->accepted = $status === 'accepted' ? 1 : 0;
        $applicant->save();

        if ($status === 'declined') {
            $this->dispatch(function () use ($applicant) {
                Mail::send('email.general-email', [
                    'title' => "Your application has been reviewed",
                    'body' => "After reviewing your application and carefully listening to the files you submitted, we’ve decided that your music isn’t the right fit for TakeTones at this time. 
<br>
This doesn’t mean your music isn’t excellent. 
<br>
Most likely, it simply means we already have too many similar tracks or we’re looking to move in a different direction with new content we add to the platform.",
                    'addons' => []
                ], function ($message) use ($applicant) {
                    $message
                        ->from('no-reply@taketones.com')
                        ->to($applicant->email)
                        ->subject("Your application has been reviewed");
                });
            });
        }

        if ($status === 'accepted') {
            $this->dispatch(function () use ($applicant) {
                Mail::send('email.general-email', [
                    'title' => "Your application has been reviewed",
                    'body' => "Thanks for applying! 
<br>
<br>
We’ve reviewed the files you submitted and would like to welcome you to become an author on TakeTones! 
<br>
We get loads of applications but accept only a limited number of authors, so congratulations!
<br>
You impressed our reviewers, and your vision seems to coincide with the direction in which we’re developing the TakeTones platform. 
<br>
We look forward to working with you! 
<br>
<br>
What’s next?
<br>
<br>
To set up your author account, register an account on the TakeTones website and send an email to authors@taketones.com containing the following information:
<br>
<br>
-  The email address you used to register your account.
<br>
-  Your PayPal or Payoneer account (Payoneer is preferable).
<br>
-  The name you would prefer to go by on TakeTones (your artist name). This is the name under which your tracks will be published.
<br>
<br>
As soon as we get an email from you with this information, we’ll send further instructions.
<br>
We hope to hear from you soon!",
                    'addons' => []
                ], function ($message) use ($applicant) {
                    $message
                        ->from('no-reply@taketones.com')
                        ->to($applicant->email)
                        ->subject("Your application has been reviewed");
                });
            });
        }

        return redirect()->back();
    }

    public function apiDeleteApplicant(AuthorApplicant $applicant)
    {
        $applicant->delete();

        return redirect()->back();
    }
}
