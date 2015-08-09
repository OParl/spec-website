<div class="col-xs-12 col-md-6">
    <h3>Ausgabeformate</h3>
    <ul>
        <li><a href="{{ route('downloads.provide', [$version->getHash(7), 'docx']) }}">Microsoft Word</a></li>
        <li><a href="{{ route('downloads.provide', [$version->getHash(7), 'epub']) }}">ePub</a></li>
        <li><a href="{{ route('downloads.provide', [$version->getHash(7), 'odt']) }}">OpenOffice.org Text</a></li>
        <li><a href="{{ route('downloads.provide', [$version->getHash(7), 'pdf']) }}">PDF</a></li>
        <li><a href="{{ route('downloads.provide', [$version->getHash(7), 'html']) }}">HTML (Standalone)</a></li>
        <li><a href="{{ route('downloads.provide', [$version->getHash(7), 'txt']) }}">Plain Text</a></li>
    </ul>
</div>
<div class="col-xs-12 col-md-6">
    <h3>Archive</h3>
    <p class="text-muted">Die Archive enthalten alle Ausgabeformate.</p>
    <ul>
        <li><a href="{{ route('downloads.provide', [$version->getHash(7), 'zip']) }}">Zip</a></li>
        <li><a href="{{ route('downloads.provide', [$version->getHash(7), 'tar.gz']) }}">Gzip</a></li>
        <li><a href="{{ route('downloads.provide', [$version->getHash(7), 'tar.bz2']) }}">Bzip2</a></li>
    </ul>
</div>
