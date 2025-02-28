<?php


namespace App\Http\Controllers\System\Partnership;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Authors\Author;
use App\Models\Finance\Balance;
use App\Models\Partner\Partner;
use App\Models\PayoutCoefficient;
use App\Models\Authors\AuthorProfile;
use App\Http\Controllers\Api\ApiController;
use App\Services\Finance\BalanceStatsService;
use App\Services\Finance\AuthorProfilesBalanceStatsService;

class PartnershipController extends ApiController
{
    public function partnershipListView(BalanceStatsService $service)
    {
        $authors = Author::all()->lazy();
        $partners = Partner::whereNotIn(
            'user_id',
            Author::select('id')
                ->get()
                ->pluck('id')
        )->get();

        $stats = [];

        $authors->each(function (Author $author) use (&$stats, $service) {
            $stats[$author->id] = $service->setUser($author)->calculateGeneralBalanceInformation();
        });

        $partners->each(function (Partner $partner) use (&$stats, $service) {
            $stats[$partner->user_id] = $service->setUser($partner->user)->calculateGeneralBalanceInformation();
        });

        $totalPartners = $authors->count() + $partners->count();

        return view('admin.partnership.list', compact('totalPartners', 'stats'));
    }

    public function payoutsView(AuthorProfilesBalanceStatsService $service)
    {
        $authors = User::whereHas('authors')->get();

        $stats = collect();

        $authors->each(function (User $author) use (&$stats, $service) {
            $service->setUser($author);

            foreach ($service->calculatePayoutInformation() as $item) {
                if (empty($item)) {
                    continue;
                }

                $stats[] = $item;
            }
        });

        $stats = $stats->filter(function ($item) {
            return !empty($item);
        });

        return $stats;
    }

    public function completePayout()
    {
        $balanceIds = request()->input('balance_id');

        foreach ($balanceIds as $id) {
            Balance::where('id', $id)->update(['status' => 'complete', 'confirmed_at' => Carbon::now()]);
        }

        return redirect()->back();
    }

    /**
     * Update value of author's payout coefficients from admin panel
     */
    public function updatePayoutCoefficients(Request $request)
    {
        $coefficient = PayoutCoefficient::where('name', $request->input('name'))->first();

        if ($coefficient) {
            $coefficient->update(['value' => $request->input('value')]);
        }
    }

    public function detailedInformationView(User $user, BalanceStatsService $service)
    {
        $service->setUser($user);

        if (request()->has('q')) {
            $query = request()->input('q');

            $profile = AuthorProfile::where('name', 'like', "%{$query}%")->first();

            $service->setProfile($profile->id);
        }

        $filters = request()->get('filters');

        $author = Author::find($user->id);

        if (isset($filters['profile_id']) && $filters['profile_id']) {
            $service->setProfile($filters['profile_id']);
        }

        $state = $service->getUserState();
        $earnings = $service->getAuthorEarnings();
        $tracks = $service->getSubmissionsStats();
        $refLinks = $user->partner->links ?? [];
        $refEarnings = $service->getReferralEarnings();

        if (isset($filters['track_id']) && $filters['track_id']) {
            $filteredTrack = $tracks[$filters['track_id']];
            $tracks = collect();
            $tracks[$filters['track_id']] = $filteredTrack;
        }

        return view('admin.partnership.detailed', compact(
            'filters',
            'author',
            'state',
            'earnings',
            'tracks',
            'refLinks',
            'refEarnings',
        ));
    }

    public function apiMakeUserPartner(User $user)
    {
        Partner::updateOrCreate([
            'user_id' => $user->id,
        ], [
            'user_id' => $user->id,
            'status' => Partner::STATUS_NEW,
        ]);

        return redirect()->route('get-users-userid-profile', ['userId' => $user->id]);
    }
}
