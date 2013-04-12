<?php
/*
 * This file is part of the phpalchemy package.
 *
 * (c) Erik Amaru Ortiz <aortiz.erik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Alchemy\Component\WebAssets;

/**
 * A file in the file system.
 *
 * This class handle assets
 *
 * @version   1.0
 * @author    Erik Amaru Ortiz <aortiz.erik@gmail.com>
 * @link      https://github.com/eriknyk/WebAssets
 * @copyright Copyright 2012 Erik Amaru Ortiz
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * @package   Alchemy/Component/WebAssets
 */
class File extends \SplFileInfo
{
    protected $content = '';

    static protected $mimeType = array(
        'ai' => 'application/postscript', 'bcpio' => 'application/x-bcpio', 'bin' => 'application/octet-stream',
        'ccad' => 'application/clariscad', 'cdf' => 'application/x-netcdf', 'class' => 'application/octet-stream',
        'cpio' => 'application/x-cpio', 'cpt' => 'application/mac-compactpro', 'csh' => 'application/x-csh',
        'csv' => 'application/csv', 'dcr' => 'application/x-director', 'dir' => 'application/x-director',
        'dms' => 'application/octet-stream', 'doc' => 'application/msword', 'drw' => 'application/drafting',
        'dvi' => 'application/x-dvi', 'dwg' => 'application/acad', 'dxf' => 'application/dxf',
        'dxr' => 'application/x-director', 'eot' => 'application/vnd.ms-fontobject', 'eps' => 'application/postscript',
        'exe' => 'application/octet-stream', 'ez' => 'application/andrew-inset',
        'flv' => 'video/x-flv', 'gtar' => 'application/x-gtar', 'gz' => 'application/x-gzip',
        'bz2' => 'application/x-bzip', '7z' => 'application/x-7z-compressed', 'hdf' => 'application/x-hdf',
        'hqx' => 'application/mac-binhex40', 'ico' => 'image/vnd.microsoft.icon', 'ips' => 'application/x-ipscript',
        'ipx' => 'application/x-ipix', 'js' => 'application/x-javascript', 'latex' => 'application/x-latex',
        'lha' => 'application/octet-stream', 'lsp' => 'application/x-lisp', 'lzh' => 'application/octet-stream',
        'man' => 'application/x-troff-man', 'me' => 'application/x-troff-me', 'mif' => 'application/vnd.mif',
        'ms' => 'application/x-troff-ms', 'nc' => 'application/x-netcdf', 'oda' => 'application/oda',
        'otf' => 'font/otf', 'pdf' => 'application/pdf',
        'pgn' => 'application/x-chess-pgn', 'pot' => 'application/mspowerpoint', 'pps' => 'application/mspowerpoint',
        'ppt' => 'application/mspowerpoint', 'ppz' => 'application/mspowerpoint', 'pre' => 'application/x-freelance',
        'prt' => 'application/pro_eng', 'ps' => 'application/postscript', 'roff' => 'application/x-troff',
        'scm' => 'application/x-lotusscreencam', 'set' => 'application/set', 'sh' => 'application/x-sh',
        'shar' => 'application/x-shar', 'sit' => 'application/x-stuffit', 'skd' => 'application/x-koan',
        'skm' => 'application/x-koan', 'skp' => 'application/x-koan', 'skt' => 'application/x-koan',
        'smi' => 'application/smil', 'smil' => 'application/smil', 'sol' => 'application/solids',
        'spl' => 'application/x-futuresplash', 'src' => 'application/x-wais-source', 'step' => 'application/STEP',
        'stl' => 'application/SLA', 'stp' => 'application/STEP', 'sv4cpio' => 'application/x-sv4cpio',
        'sv4crc' => 'application/x-sv4crc', 'svg' => 'image/svg+xml', 'svgz' => 'image/svg+xml',
        'swf' => 'application/x-shockwave-flash', 't' => 'application/x-troff',
        'tar' => 'application/x-tar', 'tcl' => 'application/x-tcl', 'tex' => 'application/x-tex',
        'texi' => 'application/x-texinfo', 'texinfo' => 'application/x-texinfo', 'tr' => 'application/x-troff',
        'tsp' => 'application/dsptype', 'ttf' => 'font/ttf',
        'unv' => 'application/i-deas', 'ustar' => 'application/x-ustar',
        'vcd' => 'application/x-cdlink', 'vda' => 'application/vda', 'xlc' => 'application/vnd.ms-excel',
        'xll' => 'application/vnd.ms-excel', 'xlm' => 'application/vnd.ms-excel', 'xls' => 'application/vnd.ms-excel',
        'xlw' => 'application/vnd.ms-excel', 'zip' => 'application/zip', 'aif' => 'audio/x-aiff', 'aifc' => 'audio/x-aiff',
        'aiff' => 'audio/x-aiff', 'au' => 'audio/basic', 'kar' => 'audio/midi', 'mid' => 'audio/midi',
        'midi' => 'audio/midi', 'mp2' => 'audio/mpeg', 'mp3' => 'audio/mpeg', 'mpga' => 'audio/mpeg',
        'ra' => 'audio/x-realaudio', 'ram' => 'audio/x-pn-realaudio', 'rm' => 'audio/x-pn-realaudio',
        'rpm' => 'audio/x-pn-realaudio-plugin', 'snd' => 'audio/basic', 'tsi' => 'audio/TSP-audio', 'wav' => 'audio/x-wav',
        'asc' => 'text/plain', 'c' => 'text/plain', 'cc' => 'text/plain', 'css' => 'text/css', 'etx' => 'text/x-setext',
        'f' => 'text/plain', 'f90' => 'text/plain', 'h' => 'text/plain', 'hh' => 'text/plain', 'htm' => 'text/html',
        'html' => 'text/html', 'm' => 'text/plain', 'rtf' => 'text/rtf', 'rtx' => 'text/richtext', 'sgm' => 'text/sgml',
        'sgml' => 'text/sgml', 'tsv' => 'text/tab-separated-values', 'tpl' => 'text/template', 'txt' => 'text/plain',
        'xml' => 'text/xml', 'avi' => 'video/x-msvideo', 'fli' => 'video/x-fli', 'mov' => 'video/quicktime',
        'movie' => 'video/x-sgi-movie', 'mpe' => 'video/mpeg', 'mpeg' => 'video/mpeg', 'mpg' => 'video/mpeg',
        'qt' => 'video/quicktime', 'viv' => 'video/vnd.vivo', 'vivo' => 'video/vnd.vivo', 'gif' => 'image/gif',
        'ief' => 'image/ief', 'jpe' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'jpg' => 'image/jpeg',
        'pbm' => 'image/x-portable-bitmap', 'pgm' => 'image/x-portable-graymap', 'png' => 'image/png',
        'pnm' => 'image/x-portable-anymap', 'ppm' => 'image/x-portable-pixmap', 'ras' => 'image/cmu-raster',
        'rgb' => 'image/x-rgb', 'tif' => 'image/tiff', 'tiff' => 'image/tiff', 'xbm' => 'image/x-xbitmap',
        'xpm' => 'image/x-xpixmap', 'xwd' => 'image/x-xwindowdump', 'ice' => 'x-conference/x-cooltalk',
        'iges' => 'model/iges', 'igs' => 'model/iges', 'mesh' => 'model/mesh', 'msh' => 'model/mesh',
        'silo' => 'model/mesh', 'vrml' => 'model/vrml', 'wrl' => 'model/vrml',
        'mime' => 'www/mime', 'pdb' => 'chemical/x-pdb', 'xyz' => 'chemical/x-pdb'
    );

    /**
     * Constructs a new file from the given path.
     *
     * @param string $path The path to the file
     */
    public function __construct($path)
    {
        if (! is_file($path)) {
            throw new \RuntimeException(sprintf(
                'Runtime Error: File Not Found: "%s"', $path
            ));
        }

        parent::__construct($path);
    }

    public function getContent()
    {
        if (! empty($thi->content)) {
            return $this->content;
        }

        $file = $this->openFile('r');

        while (!$file->eof()) {
            $this->content .= $file->fgets();
        }

        return $this->content;
    }

    /**
     * Returns the mime type of the file. (improved by erik)
     *
     * The mime type is guessed using the functions finfo(), mime_content_type()
     * and the system binary "file" (in this order), depending on which of those
     * is available on the current operating system.
     *
     * @author Erik Amaru Ortiz <aortiz.erik@gmail.com>
     * @return string|null The guessed mime type (i.e. "application/pdf")
     */
    public function getMimeType()
    {
        if (array_key_exists($this->getExtension(), self::$mimeType)) {
            return self::$mimeType[$this->getExtension()];
        }

        if (class_exists('finfo')) {
            $finfo = new \finfo;
            $mimeType = $finfo->file($this->getPathname(), FILEINFO_MIME);

            if (preg_match('/([\w\-]+\/[\w\-]+); charset=(\w+)/', $mimeType, $match)) {
                return $match[1];
            }
        }

        if (function_exists('mime_content_type')) {
            return mime_content_type($this->getPathname());
        }

        return 'application/octet-stream';
    }

    /**
     * Returns the extension of the file.
     *
     * \SplFileInfo::getExtension() is not available before PHP 5.3.6
     *
     * @return string The extension
     */
    public function getExtension()
    {
        return pathinfo($this->getBasename(), PATHINFO_EXTENSION);
    }
}
