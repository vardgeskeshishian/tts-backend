<?php

namespace App\Http\Controllers\Api\Any;

use App\Http\Controllers\Api\ApiController;
use App\Models\OrderItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class LicenseController extends ApiController
{
    /**
     * @param string $licenseNumber
     * @return Response|JsonResponse
     */
    public function getFileLicense(string $licenseNumber): Response|JsonResponse
    {
        try {
            $orderItem = OrderItem::with('license', 'orderItemable')
                ->where('license_number', $licenseNumber)
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 404);
        }

        $path = '/storage/licenses';
        if (!file_exists(base_path('/public_html'.$path)))
            mkdir(base_path('/public_html'.$path));

        $orderItem->update([
            'license_url' => $path .'/'. $licenseNumber . '.pdf'
        ]);

        $license = $orderItem->license;
        $content = $orderItem->orderItemable;

        $html = $license->sample;
        $html = str_replace('%Number_License%', $licenseNumber, $html);
        $html = str_replace('%Author_Name%', $content->authorName(), $html);
        $html = str_replace('%Product_Link%', env('APP_URL') . $content->url(), $html);
        $html = str_replace('%Product_Name%', $content->name, $html);
        $html = str_replace('%Date%', Carbon::now(), $html);

        return Pdf::loadHTML($html)->setPaper('a4')
            ->save(base_path('/public_html'.$path) .'/'. $licenseNumber . '.pdf')
            ->download($licenseNumber . '.pdf');
    }
}
