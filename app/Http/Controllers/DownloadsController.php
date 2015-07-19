<?php namespace App\Http\Controllers;

use App\Http\Requests\VersionSelectRequest;
use App\Jobs\CreateBuild;
use OParl\Spec\VersionRepository;

class DownloadsController extends Controller
{
  protected $versions = null;

  public function __construct(VersionRepository $versions)
  {
    $this->versions = $versions;
  }

  public function index(\Illuminate\Http\Request $request)
  {
    return view('downloads.index', ['versions' => $this->versions]);
  }

  public function latest($extension)
  {
    return redirect(null, 302)->route('downloads.provide', [$this->versions->latest()->getHash(7) , $extension]);
  }

  public function getFile($version, $extension)
  {
    $file = null;

    switch ($extension)
    {
      case 'docx':
      case 'pdf':
      case 'epub':
      case 'odt':
      case 'html':
      case 'txt':
        $file = storage_path('app/versions/'.$version.'/out/OParl-1.0-draft.'.$extension);
        break;

      case 'zip':
      case 'tar.gz':
      case 'tar.bz2':
        $file = storage_path('app/versions/'.$version.'/OParl-1.0-draft.'.$extension);
    }

    return response()->download(new \SplFileInfo($file), basename($file));
  }

  public function selectVersion(VersionSelectRequest $request)
  {
    if (!$this->versions->isAvailable($request->input('version')))
    {
      // fire fetch job
      $this->dispatch(new CreateBuild(
        $request->input('version'),
        $request->input('email'),
        $request->input('format')
      ));

      // send email

      // redirect to success page
      return redirect()->route('downloads.success')->with('email', $request->input('email'));
    }

    // redirect to download link
    return redirect(null, 302)->route('downloads.provide', [$request->input('version'), $request->input('format')]);
  }

  public function success()
  {
    return view('downloads.success');
  }
}