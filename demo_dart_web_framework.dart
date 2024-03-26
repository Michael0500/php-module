import 'dart:io';

void main() async {
  var server = await HttpServer.bind(InternetAddress.anyIPv6, 8089);
  await server.forEach((HttpRequest request) {
    final router = Router(request);
    final app = Application(router);

    router.httpGet('/', () => 'Home page!');
    router.httpGet('/home', () => 'Home page!');
    router.httpGet('/about', () => 'About page!');

    Function print = request.response.write;
    request.response.headers.contentType = ContentType('text', 'html');

    print('<!DOCTYPE html>');
    print('<html><head><title>Simple Dart demo framework</title></head><body>');
    print(app.run());
    print('</body></html>');
    request.response.close();
  });
}

class Router {
  Map<String, Function> _get_routes = <String, Function>{};
  Map<String, Function> _post_routes = <String, Function>{};

  final HttpRequest request;

  Router(HttpRequest this.request);

  void httpGet(String path, Function callback) {
    this._get_routes[path] = callback;
  }

  void httpPost(String path, Function callback) {
    this._post_routes[path] = callback;
  }

  String resolve() {
    final String path = this.request.uri.path;
    final String method = this.request.method;
    final Function? callback;

    if (method == 'GET') {
      callback = this._get_routes[path];
    } else if (method == 'POST') {
      callback = this._post_routes[path];
    } else {
      throw Exception('HTTP method allow GET or POST');
    }

    print('$method, $path, ${_get_routes.keys}');

    if (callback == null) {
      return 'HTTP 404 Not found';
    }

    return callback();
  }
}

class Application {
  final Router _router;

  Application(Router this._router);

  String run() {
    return this._router.resolve();
  }
}
