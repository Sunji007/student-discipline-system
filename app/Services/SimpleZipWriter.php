<?php
namespace App\Services;

class SimpleZipWriter
{
    private array $files = [];

    public function addFile(string $path, string $content): void
    {
        $this->files[$path] = $content;
    }

    public function buildZip(): string
    {
        $localHeaders = '';
        $centralDir = '';
        $offset = 0;

        // Current DOS time/date
        $time = time();
        $dosTime = ((date('H', $time) << 11) | (date('i', $time) << 5) | (date('s', $time) >> 1));
        $dosDate = (((date('Y', $time) - 1980) << 9) | (date('m', $time) << 5) | date('d', $time));

        foreach ($this->files as $path => $content) {
            $uncompressedSize = strlen($content);
            $crc = crc32($content);
            $compressed = gzdeflate($content);
            $compressedSize = strlen($compressed);

            // Local file header: 30 bytes + filename + data
            $header = pack(
                'VvvvvvVVVvv',
                0x04034b50,        // signature (4)
                20,                // version needed (2)
                0,                 // bit flag (2)
                8,                 // compression method (2: deflate)
                $dosTime,          // mod time (2)
                $dosDate,          // mod date (2)
                $crc,              // crc-32 (4)
                $compressedSize,   // compressed size (4)
                $uncompressedSize, // uncompressed size (4)
                strlen($path),     // filename length (2)
                0                  // extra field length (2)
            ) . $path . $compressed;

            $localHeaders .= $header;

            // Central directory header: 46 bytes + filename
            $cdEntry = pack(
                'VvvvvvvVVVvvvvvVV',
                0x02014b50,        // signature (4)
                20,                // version made by (2)
                20,                // version needed (2)
                0,                 // bit flag (2)
                8,                 // compression method (2)
                $dosTime,          // mod time (2)
                $dosDate,          // mod date (2)
                $crc,              // crc-32 (4)
                $compressedSize,   // compressed size (4)
                $uncompressedSize, // uncompressed size (4)
                strlen($path),     // filename length (2)
                0,                 // extra field length (2)
                0,                 // comment length (2)
                0,                 // disk start (2)
                0,                 // internal attrs (2)
                0,                 // external attrs (4)
                $offset            // relative offset of local header (4)
            ) . $path;

            $centralDir .= $cdEntry;
            $offset += strlen($header);
        }

        $centralDirSize = strlen($centralDir);
        $totalEntries = count($this->files);

        // End of central directory record: 22 bytes
        $eocd = pack(
            'VvvvvVVv',
            0x06054b50,      // signature (4)
            0,               // disk number (2)
            0,               // start disk (2)
            $totalEntries,   // entries on disk (2)
            $totalEntries,   // total entries (2)
            $centralDirSize, // central dir size (4)
            $offset,         // offset of central dir (4)
            0                // comment length (2)
        );

        return $localHeaders . $centralDir . $eocd;
    }
}
