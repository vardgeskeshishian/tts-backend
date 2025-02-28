<?php


namespace App\Http\Controllers\System;

use App\Models\VideoEffects\VideoEffect;
use Barryvdh\DomPDF\Facade\Pdf;
use PDFMerger;
use App\Models\Track;
use App\Models\License;
use App\Models\SFX\SFXPack;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\SFX\SFXTrack;
use App\Services\LicenseService;
use App\Constants\Env;
use Illuminate\Routing\Redirector;
use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\Api\ApiController;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Foundation\Application;

class LicenseController extends ApiController
{
    /**
     * @var LicenseService
     */
    private LicenseService $licenseService;

    public function __construct(LicenseService $licenseService)
    {
        parent::__construct();
        $this->licenseService = $licenseService;
    }

    public function listView()
    {
        $list = License::all();

        return view('admin.licenses.index', compact('list'));
    }

    public function createView()
    {
        return view('admin.licenses.create');
    }

    public function updateView(License $license)
    {
        return view('admin.licenses.edit', compact('license'));
    }

    /**
     * @param Request $request
     *
     * @return Application|RedirectResponse|Redirector
     * @throws ValidationException
     */
    public function createLicense(Request $request)
    {
        $license = $this->licenseService->create($request);

        return redirect("/system/content/licenses/{$license->id}");
    }

    /**
     * @param Request $request
     * @param License $license
     *
     * @return RedirectResponse
     */
    public function updateLicense(Request $request, License $license)
    {
        $this->licenseService->update($request, $license);

        return redirect()->back();
    }

    public function deleteLicense(License $license)
    {
        $this->licenseService->delete($license);

        return redirect()->back();
    }

    public function displayLicense(License $license, $type)
    {
        $item = match ($type) {
            Env::ITEM_TYPE_PACKS => SFXPack::inRandomOrder()->first(),
            Env::ITEM_TYPE_EFFECTS => SFXTrack::inRandomOrder()->first(),
            Env::ITEM_TYPE_VIDEO_EFFECTS => VideoEffect::inRandomOrder()->first(),
            default => Track::inRandomOrder()->first(),
        };

        $pdf = PDF::loadView('pdf.license', [
            'license' => $license,
            'track' => $item,
            'license_number' => Str::random(),
            'pdf_type' => $type,
        ]);

        $merger = PDFMerger::init();
        $merger->addPDFString($pdf->output(), 'all', 'P');
        $merger->merge();

        return $merger->inline();
    }
}
