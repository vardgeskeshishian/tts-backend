<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use JetBrains\PhpStorm\NoReturn;

/**
 * Description of VideoStream
 *
 * @author Rana
 * @link http://codesamplez.com/programming/php-html5-video-streaming-tutorial
 */
class AudioStreamingService
{
    private $path;
    private $stream = "";
    private $storage;

    public function __construct($filePath)
    {
        $this->storage = Storage::disk('spaces.common');
        $this->path = $filePath;
    }

    /**
     * Open stream
     */
    private function open()
    {
        if (!($this->stream = $this->storage->readStream($this->path))) {
            die('Could not open stream for reading');
        }
    }

    /**
     * Set proper header to serve the video content
     */
    private function setHeader()
    {
        header('Accept-Ranges: bytes');
        header('Content-Type: audio/mpeg');
        header("Content-Disposition: inline");

        $byteOffset = 0;
        $byteLength = $fileSize = $this->storage->size($this->path);

        ### Parse Content-Range header for byte offsets, looks like "bytes=11525-" OR "bytes=11525-12451"
        if (isset($_SERVER['HTTP_RANGE']) && preg_match('%bytes=(\d+)-(\d+)?%i', $_SERVER['HTTP_RANGE'], $match)) {
            ### Offset signifies where we should begin to read the file
            $byteOffset = (int)$match[1];
            ### Length is for how long we should read the file according to the browser, and can never go beyond the file size
            if (isset($match[2])) {
                $finishBytes = (int)$match[2];
                $byteLength = $finishBytes + 1;
            } else {
                $finishBytes = $fileSize - 1;
            }

            $cr_header = sprintf('Content-Range: bytes %d-%d/%d', $byteOffset, $finishBytes, $fileSize);

            header("HTTP/1.1 206 Partial content");
            header($cr_header);  ### Decrease by 1 on byte-length since this definition is zero-based index of bytes being sent
        }

        $byteRange = $byteLength - $byteOffset;
        header(sprintf('Content-Length: %d', $byteRange));
        header("Cache-Control: max-age=2592000, public");
        header("Expires: " . gmdate('D, d M Y H:i:s', time() + 2592000) . ' GMT');
        header("Last-Modified: " . $this->storage->lastModified($this->path));

        fseek($this->stream, $byteOffset);

        $bufferSize = 512 * 16; ### Just a reasonable buffer size
        $bytePool = $byteRange; ### Contains how much is left to read of the byteRange

        while ($bytePool > 0) {
            $chunkSizeRequested = min($bufferSize, $bytePool); ### How many bytes we request on this iteration
            ### Try readin $chunkSizeRequested bytes from $handle and put data in $buffer
            $buffer = fread($this->stream, $chunkSizeRequested);
            ### Store how many bytes were actually read
            $chunkSizeActual = strlen($buffer);
            ### If we didn't get any bytes that means something unexpected has happened since $bytePool should be zero already
            if ($chunkSizeActual === 0) {
                ### For production servers this should go in your php error log, since it will break the output
                break;
            }
            ### Decrease byte pool with amount of bytes that were read during this iteration
            $bytePool -= $chunkSizeActual;
            ### Write the buffer to output
            print $buffer;
            ### Try to output the data to the client immediately
            flush();
        }
    }

    /**
     * close curretly opened stream
     */
    #[NoReturn] private function end()
    {
        fclose($this->stream);
        exit;
    }

    /**
     * Start streaming video content
     */
    public function start()
    {
        $this->open();
        $this->setHeader();
        $this->end();
    }
}
