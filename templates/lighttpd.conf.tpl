server.document-root = "{doc_root}"
server.bind = "{host}"
server.port = {port}

mimetype.assign = (
  ".html" => "text/html",
  ".txt" => "text/plain",
  ".jpg" => "image/jpeg",
  ".png" => "image/png"
)

server.modules = (
    "mod_fastcgi",
    "mod_rewrite",
    "mod_compress",
    "mod_setenv"
)

compress.cache-dir = "{tmp_dir}"
compress.filetype  = ("text/plain","text/css", "text/xml", "text/html", "text/javascript")
compress.allowed-encodings = ("bzip2", "gzip", "deflate")

fastcgi.server = ( ".php" => ((
    "bin-path" => "{bin_path}",
    "socket" => "{socket_path}"
)))

setenv.add-environment = (
  "TRAC_ENV" => "lighttpd",
  "PHPALCHEMY_ENV" => "{environment}"
)

url.rewrite-once = (
  "^/(.*)" => "/web/app.php"
)

static-file.exclude-extensions = (".fcgi", ".php", ".rb", "~", ".inc")
index-file.names = ("index.html")

