#
# lighttpd configuration file
#

server.document-root = "{doc_root}"
server.bind = "{host}"
server.port = {port}

# mimetype mapping
mimetype.assign = (
  ".pdf"          =>      "application/pdf",
  ".sig"          =>      "application/pgp-signature",
  ".spl"          =>      "application/futuresplash",
  ".class"        =>      "application/octet-stream",
  ".ps"           =>      "application/postscript",
  ".torrent"      =>      "application/x-bittorrent",
  ".dvi"          =>      "application/x-dvi",
  ".gz"           =>      "application/x-gzip",
  ".pac"          =>      "application/x-ns-proxy-autoconfig",
  ".swf"          =>      "application/x-shockwave-flash",
  ".tar.gz"       =>      "application/x-tgz",
  ".tgz"          =>      "application/x-tgz",
  ".tar"          =>      "application/x-tar",
  ".zip"          =>      "application/zip",
  ".mp3"          =>      "audio/mpeg",
  ".m3u"          =>      "audio/x-mpegurl",
  ".wma"          =>      "audio/x-ms-wma",
  ".wax"          =>      "audio/x-ms-wax",
  ".ogg"          =>      "application/ogg",
  ".wav"          =>      "audio/x-wav",
  ".gif"          =>      "image/gif",
  ".jpg"          =>      "image/jpeg",
  ".jpeg"         =>      "image/jpeg",
  ".png"          =>      "image/png",
  ".xbm"          =>      "image/x-xbitmap",
  ".xpm"          =>      "image/x-xpixmap",
  ".xwd"          =>      "image/x-xwindowdump",
  ".css"          =>      "text/css",
  ".html"         =>      "text/html",
  ".htm"          =>      "text/html",
  ".js"           =>      "text/javascript",
  ".asc"          =>      "text/plain",
  ".c"            =>      "text/plain",
  ".cpp"          =>      "text/plain",
  ".log"          =>      "text/plain",
  ".conf"         =>      "text/plain",
  ".text"         =>      "text/plain",
  ".txt"          =>      "text/plain",
  ".dtd"          =>      "text/xml",
  ".xml"          =>      "text/xml",
  ".mpeg"         =>      "video/mpeg",
  ".mpg"          =>      "video/mpeg",
  ".mov"          =>      "video/quicktime",
  ".qt"           =>      "video/quicktime",
  ".avi"          =>      "video/x-msvideo",
  ".asf"          =>      "video/x-ms-asf",
  ".asx"          =>      "video/x-ms-asf",
  ".wmv"          =>      "video/x-ms-wmv",
  ".bz2"          =>      "application/x-bzip",
  ".tbz"          =>      "application/x-bzip-compressed-tar",
  ".tar.bz2"      =>      "application/x-bzip-compressed-tar"
)

# Use the "Content-Type" extended attribute to obtain mime type if possible
mimetype.use-xattr = "enable"

server.modules = (
    "mod_fastcgi",
    "mod_rewrite",
    "mod_compress",
    "mod_setenv"
)

compress.cache-dir = "{tmp_dir}"
compress.filetype  = ("text/plain","text/css", "text/xml", "text/html", "text/javascript")
compress.allowed-encodings = ("bzip2", "gzip", "deflate")

server.errorlog = "{tmp_dir}_lighttpd.error.log"

fastcgi.server = ( ".php" => ((
    "bin-path" => "{bin_path}",
    "socket" => "{socket_path}"
)))

setenv.add-environment = (
  "TRAC_ENV" => "lighttpd",
  "PHPALCHEMY_ENV" => "{environment}"
)

server.tag = "lighttpd server / Powered by PHPAlchemy Framework"

#url.rewrite-once = (
#  "^/(.*)" => "/app.php"
#)

#url.rewrite-once = (
#    ".*\.(jpg|jpeg|gif|png|swf|avi|mpg|mpeg|mp3|flv|ico|css)$" => "$0",
#    "^/(.+)/?$" => "/app.php"
#)

url.rewrite-once = (
  "^/(.*)\.(.+)$" => "$0",
  "^/(.+)?$" => "/app.php"
)

static-file.exclude-extensions = (".fcgi", ".php", ".rb", "~", ".inc")

# files to check for if .../ is requested
index-file.names = ("index.php", "index.html")

