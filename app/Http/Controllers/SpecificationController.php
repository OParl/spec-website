<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Filesystem\Filesystem;
use OParl\Spec\BuildRepository;
use OParl\Spec\LiveVersionRepository;

class SpecificationController extends Controller
{
    /**
     * Show the specification's live copy.
     *
     * @param LiveVersionRepository $liveversion
     *
     * @return \Illuminate\Http\Response
     */
    public function index(LiveVersionRepository $liveversion)
    {
        $title = 'Spezifikation';
        return view('specification.index', compact('liveversion', 'title'));
    }

    public function builds(BuildRepository $build) {
        return response()->json($build->getLatest(15));
    }

    public function imageIndex()
    {
        abort(404);
    }

    public function image(Filesystem $fs, $image)
    {
        $imageData = $fs->get(LiveVersionRepository::getImagesPath($image));

        return response($imageData, 200, ['Content-type' => 'image/png']);
    }

    public function raw(LiveVersionRepository $livecopy)
    {
        return response($livecopy->getRaw(), 200, ['Content-type' => 'text/plain']);
    }
}
