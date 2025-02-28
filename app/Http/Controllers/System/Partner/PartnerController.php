<?php

namespace App\Http\Controllers\System\Partner;

use App\Models\Partner\Partner;
use App\Http\Controllers\Api\ApiController;
use App\Models\Partner\PartnerPaymentsHistory;

class PartnerController extends ApiController
{
    public function getPartnersView()
    {
        $list = Partner::paginate();

        return view('admin.partner.list', compact('list'));
    }

    public function getPartnerView($partner)
    {
        $partner = Partner::find($partner)->load('earnings', 'payouts');

        return view('admin.partner.single', compact('partner'));
    }

    public function getPayoutsView()
    {
        $history = PartnerPaymentsHistory::whereStatus(PartnerPaymentsHistory::STATUS_PLANNED)
            ->with('partner')
            ->get();

        return view('admin.partner.payouts', compact('history'));
    }

    public function doPayout($payoutId)
    {
        $pph = PartnerPaymentsHistory::find($payoutId);
        $pph->status = PartnerPaymentsHistory::STATUS_FINISHED;
        $pph->save();

        return redirect()->back();
    }
}
