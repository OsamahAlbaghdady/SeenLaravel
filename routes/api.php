<?php

use App\Http\Controllers\Api\AuthController;
use Carbon\Carbon;
use FFMpeg\Coordinate\TimeCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;


use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;
use FFMpeg\Filters\Video\VideoFilters;
use FFMpeg\Format\Video\WebM;
use FFMpeg\Format\Video\X264;
use Intervention\Image\Facades\Image;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});



Route::controller(AuthController::class)->group(function () {
    Route::post('register', 'register');
    Route::post('login', 'login');
});




Route::post(
    'fmpeg',
    function (Request $request) {


        $ffmpeg = FFMpeg::create([
            'ffmpeg.binaries'  => '/opt/homebrew/bin/ffmpeg',
            'ffprobe.binaries' => '/opt/homebrew/bin/FFProbe'
        ]);
        $ffprobe = FFProbe::create([
            'ffmpeg.binaries'  => '/opt/homebrew/bin/ffmpeg',
            'ffprobe.binaries' => '/opt/homebrew/bin/FFProbe'
        ]);
        $video_extensions = ['mp4', 'mpeg', 'mpeg4', 'mov', 'webm', 'avi'];


        if ($request->files) {

            foreach ($request->file('files') as $file) {

                $thub =  'thub' . Carbon::now() . 'th.jpg';
                $video = $ffmpeg->open($file);
                $video->frame(TimeCode::fromSeconds(2))->save('Attachments/thub/' . $thub);

                $destinationPath = public_path('Attachments/thub');
                $img = Image::make(public_path('Attachments/thub/') . $thub);
                $height = Image::make($img)->height();
                $width = Image::make($img)->width();


                $img->fit(360)->save($destinationPath . '/' . $thub);

                $name = 'p' . Carbon::now() . 'test.mp4';

                $bitRate = $ffprobe->streams($file)->videos()->first()->get('bit_rate');

                $format = new X264();
                $format->setAdditionalParameters(array('-vf', 'zscale=t=linear:npl=100,format=gbrpf32le,zscale=p=bt709,tonemap=tonemap=hable:desat=0,zscale=t=bt709:m=bt709:r=tv,format=yuv420p'));

                if ($bitRate > 500000) {
                    $format->setKiloBitrate(500);
                } else {
                    $format->setKiloBitrate($bitRate / 1000);
                }

                if ($width > $height) {
                    if ($width < 854) {
                        $newWidth = $width;
                        $newHeight = $height;
                    } else {
                        $newWidth = 854;
                        $newHeight = ($height / $width) * 854;
                    }
                } else {
                    if ($height < 854) {
                        $newWidth = $width;
                        $newHeight = $height;
                    } else {
                        $newWidth = ($width / $height) * 854;
                        $newHeight = 854;
                    }
                }
                $video->filters()
                    ->resize(new \FFMpeg\Coordinate\Dimension((int)$newWidth, (int)$newHeight))
                    ->synchronize();


                $video->save($format, base_path() . '/public/Attachments/' . $name);
                return response()->json(['url' => url('/Attachments/' . $name)]);
            }
        }
    }
);


Broadcast::routes(['middleware' => ['auth:sanctum']]);
