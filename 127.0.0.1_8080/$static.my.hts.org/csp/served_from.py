# run this server by using the command below:
#
# python ./served_from.py 127.0.0.1 8001 &
#
# stop it with 'kill pid' (get pid with 'jobs')
#
#
# It is intended to be used as a sample usage of the
# G-WAN reverse-proxy Handler

import sys,BaseHTTPServer as B
class Handler(B.BaseHTTPRequestHandler):
  def do_GET(self):
    self.wfile.write("Python HTTP server listening on port %s" % port)
  def log_message(self, *args):
    pass
if __name__ == '__main__':
  host,port = sys.argv[1:3]
  server = B.HTTPServer((host,int(port)), Handler)
  server.serve_forever()

