<?php

namespace App\Http\Controllers\Api\Authorized;

use App\Http\Resources\Authorized\AuthorResource;
use App\Models\Authors\AuthorProfile;
use App\Models\PayoutCoefficient;
use App\Models\Track;
use App\Models\UserDownloads;
use App\Models\VideoEffects\VideoEffect;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Collection;
use Mail;
use Carbon\Carbon;
use App\Models\Authors\Author;
use App\Constants\FinancesEnv;
use App\Models\Finance\Balance;
use App\Constants\SubmissionsEnv;
use Illuminate\Http\JsonResponse;
use App\Models\Authors\AuthorApplicant;
use App\Models\Authors\AuthorSubmission;
use App\Models\SubscriptionHistory;
use App\Services\FilesService;
use App\Http\Requests\AuthorApplyRequest;
use App\Http\Controllers\Api\ApiController;
use App\Services\Finance\BalanceStatsService;
use App\Services\TracksService;
use App\Services\VideoEffectsService;
use App\Http\Requests\AuthorSubmitTrackRequest;
use App\Models\Authors\AuthorSubmissionComment;
use Illuminate\Support\Facades\DB;

class AuthorController extends ApiController
{
    public function __construct(
        public TracksService       $tracksService,
        public VideoEffectsService $videoEffectsService
    )
    {
    }

    public function apply(AuthorApplyRequest $request): JsonResponse
    {
        $applicant = AuthorApplicant::make();
        $applicant->fill($request->all());
        $applicant->save();

        $name = $request->input('full_name');
        $email = $request->input('email');

        $this->dispatch(function () use ($email) {
            Mail::send('email.general-email', [
                'title' => "New Author: {$email}",
                'body' => 'there is a new request to become an author',
                'addons' => [],
            ], function ($message) use ($email) {
                $message->to('authors@taketones.com')->subject("New Author: {$email}");
            });
        });

        return $this->success([
            'success' => true,
        ]);
    }

    public function newSubmission(AuthorSubmitTrackRequest $request): LengthAwarePaginator|AnonymousResourceCollection
    {
        $this->checkAccess();

        /**
         * @var $author Author
         */
        $author = $this->user();

        $fileService = resolve(FilesService::class);

        $trackFileLink = null;

        if ($request->files->has('track_file')) {
            $file = $request->file('track_file');
            $trackFileLink = $fileService
                ->setConfig('submissions', $file)
                ->cloudUpload();
        }

        $submission = AuthorSubmission::create([
            'user_id' => $author->id,
            'track_name' => $request->input('track_name'),
            'track_link' => $request->input('track_link'),
            'track_file' => $trackFileLink,
            'exclusive' => $request->input('exclusive'),
            'reviewer_status' => SubmissionsEnv::STATUS_NEW,
            'final_status' => SubmissionsEnv::STATUS_NEW,
            'has_content_id' => $request->boolean('has_content_id'),
        ]);

        $this->dispatch(function () use ($submission, $author) {
            Mail::send('email.general-email', [
                'title' => "New Track",
                'body' => " {$submission->track_name} by {$author->email}",
                'addons' => [],
            ], function ($message) use ($submission, $author) {
                $message->to('authors@taketones.com')->subject("New Track");
            });
        });

        return $this->submissions();
    }

    /** list of submissions
     *
     * @OA\Get(
     *     path="/v1/protected/authors/submissions",
     *     summary="List of submissions",
     *     tags={"Authors"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              @OA\Property(property="current_page", type="integer", example="1"),
     *              @OA\Property(property="data", type="array", @OA\Items(
     *                  @OA\Property(property="id", type="integer", example="123"),
     *                  @OA\Property(property="user_id", type="integer", example="123"),
     *                  @OA\Property(property="track_name", type="string", example="Inspiring Corporate"),
     *                  @OA\Property(property="track_link", type="string", example="https://audiojungle.net/item/inspiring-corporate/23172786"),
     *                  @OA\Property(property="track_file", type="string", example="/storage/files/073d6f2167f8d9a96d86b9fe6b9d5b37/d8b2dc5853c6f1046e008a41b3f60565.wav"),
     *                  @OA\Property(property="exclusive", type="boolean"),
     *                  @OA\Property(property="reviewer_status", type="string", example="new"),
     *                  @OA\Property(property="final_status", type="string", example="published"),
     *                  @OA\Property(property="created_at", type="string", example="2024-02-27T06:20:59.000000Z"),
     *                  @OA\Property(property="updated_at", type="string", example="2024-02-27T06:20:59.000000Z"),
     *                  @OA\Property(property="track_id", type="integer", example="123"),
     *                  @OA\Property(property="payed", type="integer", example="0"),
     *                  @OA\Property(property="zip_file", type="string", example="/storage/files/073d6f2167f8d9a96d86b9fe6b9d5b37/2523f0e6a68b801a9154c1b0262e7ebd.zip"),
     *                  @OA\Property(property="tempo", type="float", example="104,22"),
     *                  @OA\Property(property="description", type="string", example="Bright & Resolute cinematic music for corporate videos and advertising."),
     *                  @OA\Property(property="tags", type="string", example="calm, landscape, life, loss, love"),
     *                  @OA\Property(property="has_content_id", type="boolean", example="false"),
     *                  @OA\Property(property="public_comments", type="array", @OA\Items(
     *                      @OA\Property(property="id", type="integer", example="5"),
     *                      @OA\Property(property="submission_id", type="integer", example="5"),
     *                      @OA\Property(property="comment", type="string", example="Пусть будет Soft Light"),
     *                      @OA\Property(property="comment_type", type="string", example="public"),
     *                      @OA\Property(property="user_id", type="integer", example="5"),
     *                      @OA\Property(property="created_at", type="string", example="2024-02-27T06:20:59.000000Z"),
     *                      @OA\Property(property="updated_at", type="string", example="2024-02-27T06:20:59.000000Z"),
     *                      @OA\Property(property="commenter_role", type="string", example="author"),
     *                      @OA\Property(property="user", type="object",
     *                          ref="#/components/schemas/User"
     *                      )
     *                  )),
     *              )),
     *         ),
     *     ),
     * )
     *
     */
    public function submissions(): LengthAwarePaginator|AnonymousResourceCollection
    {
        $this->checkAccess();

        return $this->pagination(AuthorSubmission::class, [], ['user_id' => $this->user()->id], ['publicComments'], 20);
    }

    public function commentOnSubmission(AuthorSubmission $submission): JsonResponse
    {
        $this->checkAccess();

        AuthorSubmissionComment::create([
            'submission_id' => $submission->id,
            'comment' => request()->input('comment'),
            'comment_type' => SubmissionsEnv::COMMENT_TYPE_PUBLIC,
            'user_id' => $this->user()->id,
        ]);

        $submission = $submission->load('publicComments');

        return $this->success($submission);
    }

    public function reUploadSubmissionFile(AuthorSubmission $submission): LengthAwarePaginator|AnonymousResourceCollection
    {
        $this->checkAccess();

        $filesService = resolve(FilesService::class);

        $file = request()->file('track_file');

        $trackFileLink = $filesService
            ->setConfig('submissions', $file)
            ->cloudUpload();
        $submission->track_file = $trackFileLink;
        $submission->final_status = SubmissionsEnv::STATUS_RESUB;
        $submission->save();

        $this->dispatch(function () use ($submission) {
            Mail::send('email.general-email', [
                'title' => "Track {$submission->track_name} by {$submission->author->email} changed status",
                'body' => "New status: {$submission->final_status}",
                'addons' => [],
            ], function ($message) use ($submission) {
                $message
                    ->from('no-reply@taketones.com')
                    ->to('authors@taketones.com')
                    ->subject("Track {$submission->track_name} by {$submission->author->email} changed status");
            });
        });

        return $this->submissions();
    }

    public function deleteSubmission(AuthorSubmission $submission): LengthAwarePaginator|AnonymousResourceCollection
    {
        $this->checkAccess();

        $submission->delete();

        return $this->submissions();
    }

    public function submitTrack(AuthorSubmission $submission): JsonResponse
    {
        $this->checkAccess();

        $filesService = resolve(FilesService::class);

        $zipLink = null;
        $file = request()->file('zip_file');

        if ($file) {
            $zipLink = $filesService
                ->setConfig('submissions', $file)
                ->cloudUpload();
        }

        $submission->final_status = SubmissionsEnv::STATUS_DELIVERY_C;
        $submission->zip_file = $zipLink;
        $submission->tempo = request()->input('tempo');
        $submission->description = request()->input('description');
        $submission->tags = request()->input('tags');
        $submission->save();

        $this->dispatch(function () use ($submission) {
            Mail::send('email.general-email', [
                'title' => "Track {$submission->track_name} by {$submission->author->email} changed status",
                'body' => "New status: {$submission->final_status}",
                'addons' => [],
            ], function ($message) use ($submission) {
                $message
                    ->from('no-reply@taketones.com')
                    ->to('authors@taketones.com')
                    ->subject("Track {$submission->track_name} by {$submission->author->email} changed status");
            });
        });

        return $this->success($submission);
    }

    /**
     * @OA\Post(
     *     path = "/v1/protected/authors/earnings",
     *     summary = "Get Earnings Table",
     *     tags={"Authors"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\MediaType(mediaType="multipart/form-data", @OA\Schema(
     *          @OA\Property(property="start_date", type="string", description="Start Date", example="YYYY-MM-DD"),
     *          @OA\Property(property="end_date", type="string", description="End Date", example="YYYY-MM-DD"),
     *          @OA\Property(property="profile_id", type="integer", description="Author Id"),
     *     ))),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="author_balance", type="integer", example="0"),
     *                  @OA\Property(property="partner_balance", type="integer", example="0"),
     *                  @OA\Property(property="total_balance", type="integer", example="0"),
     *                  @OA\Property(property="earnings", type="array", @OA\Items(
     *                      @OA\Property(property="transactions", type="object",
     *                          @OA\Property(property="date", type="string", example="dd.mm.yyyy"),
     *                          @OA\Property(property="count", type="integer", example="0"),
     *                          @OA\Property(property="bonus", type="float", example="10.02"),
     *                      ),
     *                      @OA\Property(property="subscriptions", type="object",
     *                          @OA\Property(property="date", type="string", example="dd.mm.yyyy"),
     *                          @OA\Property(property="count", type="integer", example="0"),
     *                          @OA\Property(property="bonus", type="float", example="10.02"),
     *                      ),
     *                  )),
     *                  @OA\Property(property="graph", type="array", @OA\Items(
     *                      @OA\Property(property="data", type="object",
     *                          @OA\Property(property="transactions", type="object",
     *                              @OA\Property(property="date", type="string", example="dd.mm.yyyy"),
     *                              @OA\Property(property="count", type="integer", example="0"),
     *                              @OA\Property(property="bonus", type="float", example="10.02"),
     *                          ),
     *                          @OA\Property(property="subscriptions", type="object",
     *                              @OA\Property(property="date", type="string", example="dd.mm.yyyy"),
     *                              @OA\Property(property="count", type="integer", example="0"),
     *                              @OA\Property(property="bonus", type="float", example="10.02"),
     *                          ),
     *                      )
     *                  )),
     *                  @OA\Property(property="map", type="array", @OA\Items(
     *                      @OA\Property(property="CA", type="object",
     *                          @OA\Property(property="transactions", type="integer", example="0"),
     *                          @OA\Property(property="subscriptions", type="integer", example="0"),
     *                          @OA\Property(property="bonus", type="float", example="10.02"),
     *                      )
     *                  )),
     *                  @OA\Property(property="top", type="array", @OA\Items(
     *                      @OA\Property(property="transactions", type="integer", example="0"),
     *                      @OA\Property(property="subscriptions", type="integer", example="0"),
     *                      @OA\Property(property="bonus", type="float", example="10.02"),
     *                      @OA\Property(property="country", type="string", example="CA"),
     *                  )),
     *              )
     *         ),
     *     ),
     * )
     *
     * @param BalanceStatsService $statsService
     * @return JsonResponse
     * @throws \Exception
     */
    public function getEarningsTable(BalanceStatsService $statsService): JsonResponse
    {
        $this->checkAccess();
        $statsService->setUser($this->user());

        $start = request()->input('start_date');
        $end = request()->input('end_date');

        if (request()->has('profile_id')) {
            $statsService->setProfile(request()->input('profile_id'));
        }

        return $this->success(array_merge($statsService->getCurrentBalance(), $statsService->getPortfolioHistory($start, $end)));
    }

    /**
     * @OA\Get(
     *     path = "/v1/protected/authors/portfolio",
     *     summary = "Get Portfolio Table",
     *     tags={"Authors"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              @OA\Property(type="array", @OA\Items(
     *                      @OA\Property(property="author_id", type="integer"),
     *                      @OA\Property(property="author_name", type="string"),
     *                      @OA\Property(property="content", type="array", @OA\Items(
     *                          @OA\Property(property="id", type="integer"),
     *                          @OA\Property(property="name", type="string"),
     *                          @OA\Property(property="slug", type="string"),
     *                          @OA\Property(property="category", type="string"),
     *                          @OA\Property(property="sales", type="integer"),
     *                          @OA\Property(property="downloads", type="integer"),
     *                          @OA\Property(property="rate", type="string"),
     *                          @OA\Property(property="total", type="integer"),
     *                      )),
     *              ))
     *         ),
     *     ),
     * )
     *
     * @return JsonResponse
     */
    public function getPortfolioTable(): JsonResponse
    {
        $authors = auth()->user()->authors()->get();

        $coefficients = PayoutCoefficient::pluck('value', 'name')->toArray();

        $result = [];

        $sumSubscriptionsCurrentMonth = SubscriptionHistory::select([
            DB::raw('sum(payment) as earnings'),
            DB::raw('DATE_FORMAT(created_at,"%Y-%m") as months')
        ])->whereDate('created_at', '>=', Carbon::now()->startOfMonth())
            ->first();

        $countDonwloadsTrack = UserDownloads::whereIn('license_id', [12, 13])
            ->where('class', Track::class)
            ->whereDate('created_at', '>=', Carbon::now()->startOfMonth())
            ->count();

        $countDownloadsVideoEffects = UserDownloads::whereIn('license_id', [12, 13])
            ->where('class', VideoEffect::class)
            ->whereDate('created_at', '>=', Carbon::now()->startOfMonth())
            ->count();

        $downloadOneTrack = $countDonwloadsTrack ? $sumSubscriptionsCurrentMonth->earnings * (1 - $coefficients['fee'])
            * $coefficients['wmusic'] / $countDonwloadsTrack : 0;
        $downloadOneVideoEffect = $countDownloadsVideoEffects ? $sumSubscriptionsCurrentMonth->earnings * (1 - $coefficients['fee'])
            * $coefficients['wvideo'] / $countDownloadsVideoEffects : 0;

        foreach ($authors as $author) {
            $tracks = $this->tracksService->getTracksForPortfolio($author->id, $coefficients, $downloadOneTrack);

            $videoEffects = $this->videoEffectsService->getVideoEffectsForPortfolio($author->id, $coefficients, $countDownloadsVideoEffects);

            $result[] = [
                'author_id' => $author->id,
                'author_name' => $author->name,
                'content' => array_merge($tracks, $videoEffects)
            ];

        }

        return response()->json($result);
    }

    /**
     * @OA\Post(
     *     path = "/v1/protected/authors/statement",
     *     summary = "Get Statement Table",
     *     tags={"Authors"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\MediaType(mediaType="multipart/form-data", @OA\Schema(
     *          @OA\Property(property="start_date", type="string", description="Start Date", example="YYYY-MM-DD"),
     *          @OA\Property(property="end_date", type="string", description="End Date", example="YYYY-MM-DD"),
     *          @OA\Property(property="profile_id", type="integer", description="Author Id"),
     *     ))),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="statement", type="object",
     *                      @OA\Property(property="itemIds", type="array", @OA\Items(type="integer", example="10")),
     *                  ),
     *                  @OA\Property(property="earnings", type="array", @OA\Items(
     *                      @OA\Property(property="date", type="string", example="dd.mm.yyyy"),
     *                      @OA\Property(property="track", type="object",
     *                          ref="#/components/schemas/TrackResource"
     *                      ),
     *                      @OA\Property(property="details", type="string"),
     *                      @OA\Property(property="rate", type="integer", example="10"),
     *                      @OA\Property(property="discount", type="integer", example="10"),
     *                      @OA\Property(property="earnings", type="float", example="79.50"),
     *                  )),
     *                  @OA\Property(property="payouts", type="array", @OA\Items(
     *                      @OA\Property(property="date", type="string", example="dd.mm.yyyy"),
     *                      @OA\Property(property="type", type="string"),
     *                      @OA\Property(property="email", type="string"),
     *                      @OA\Property(property="monthly_total", type="integer"),
     *                      @OA\Property(property="status", type="string"),
     *                      @OA\Property(property="updated_at", type="string", example="dd.mm.yyyy"),
     *                  )),
     *              )
     *         ),
     *     ),
     * )
     *
     *
     * @param BalanceStatsService $statsService
     * @return JsonResponse
     * @throws \Exception
     */
    public function getStatementTable(BalanceStatsService $statsService): JsonResponse
    {
        $this->checkAccess();
        $statsService->setUser($this->user());

        $start = request()->input('start_date');
        $end = request()->input('end_date');

        if (request()->has('profile_id')) {
            $statsService->setProfile(request()->input('profile_id'));
        }

        return $this->success([
            'statement' => $statsService->getStatements($start, $end),
        ]);
    }

    /**
     * @OA\Get(
     *      path="/v1/protected/authors/payouts",
     *      summary="Get Payouts Table",
     *      tags={"Authors"},
     *      security={{"bearerAuth":{}}},
     *      @OA\Response(
     *          response="200",
     *          description="Success",
     *          @OA\JsonContent(
     *               @OA\Property(property="data", type="object",
     *                    @OA\Property(property="payouts", type="array", @OA\Items(
     *                         @OA\Property(property="id", type="integer", example="1"),
     *                         @OA\Property(property="month", type="integer", example="1714521600"),
     *                         @OA\Property(property="type", type="string", example="payoneer"),
     *                         @OA\Property(property="payment_email", type="string", example="email@gmail.com"),
     *                         @OA\Property(property="monthly_total", type="integer", example="13.32"),
     *                         @OA\Property(property="status", type="string", example="awaiting"),
     *                         @OA\Property(property="update", type="integer", example="1714521600"),
     *                    )),
     *                    @OA\Property(property="total", type="integer", example="44.22"),
     *                )
     *          ),
     *      ),
     *  )
     * @param BalanceStatsService $statsService
     * @return JsonResponse
     */
    public function getPayoutsTable(BalanceStatsService $statsService): JsonResponse
    {
        return response()->json($statsService->getPayouts($this->user()));
    }

    /**
     * @OA\Post(
     *     path = "/v1/protected/authors/payout-settings",
     *     summary = "Set Payout Setting",
     *     tags={"Authors"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\MediaType(mediaType="multipart/form-data", @OA\Schema(
     *          @OA\Property(property="paypal", type="string", description="Paypal Email"),
     *          @OA\Property(property="payoneer", type="string", description="Payoneer Email"),
     *     ))),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              @OA\Property(property="data", type="object",
     *                  ref="#/components/schemas/User"
     *              )
     *         ),
     *     ),
     * )
     *
     * @return JsonResponse
     */
    public function setPayoutSetting(): JsonResponse
    {
        $paypal = request()->input('paypal');
        $payoneer = request()->input('payoneer');

        $this->user()->paypal_account = $paypal ?: null;
        $this->user()->payoneer_account = $payoneer ?: null;
        $this->user()->save();

        Balance::where('date', Carbon::now()->format(FinancesEnv::BALANCE_DATE_FORMAT))
            ->where('status', 'awaiting')
            ->where('user_id', $this->user()->id)
            ->update([
                'payment_type' => $paypal ? 'paypal' : 'payoneer',
                'payment_email' => $paypal ?: $payoneer,
                'updated_at' => Carbon::now(),
            ]);

        return $this->success($this->user());
    }

    private function checkAccess()
    {
        abort_if(!$this->user(), 403);
        abort_if(!$this->user()->isAuthor(), 403);
    }

    /**
     * @OA\Get(
     *     path="/v1/protected/authors",
     *     summary="List Authors",
     *     tags={"Authors"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              @OA\Property(property="items", type="array", @OA\Items(
     *                  ref="#/components/schemas/AuthorizedAuthorResource"
     *              )),
     *              @OA\Property(property="current_page", type="integer", example="1"),
     *              @OA\Property(property="next_page_url", type="string"),
     *              @OA\Property(property="path", type="string"),
     *              @OA\Property(property="per_page", type="integer", example="1"),
     *              @OA\Property(property="prev_page_url", type="string"),
     *              @OA\Property(property="to", type="integer", example="1"),
     *              @OA\Property(property="total", type="integer", example="1"),
     *         ),
     *     ),
     * )
     *
     * @return Collection
     */
    public function get(): Collection
    {
        return AuthorProfile::where('user_id', auth()->user()->id)
            ->get()->map(fn($item) => new AuthorResource($item));
    }
}
