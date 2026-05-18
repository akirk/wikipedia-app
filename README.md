# Wikipedia

A WordPress app powered by [WpApp](https://github.com/akirk/wp-app).

## Wikimedia API usage

The app reads live Wikipedia data through the Wikimedia Action API. To avoid
overloading Wikimedia services, requests are kept user-driven and cacheable:

- Search responses are cached briefly in WordPress transients.
- Article metadata and article HTML are cached longer, while explicit article
  refreshes bypass the cache.
- Live browser search waits before requesting, ignores one-character searches,
  and cancels superseded searches.
- API requests use JSON, include `origin=*` for CORS, and surface Wikimedia API
  errors and `Retry-After` rate-limit responses instead of hiding them behind a
  generic HTTP error.
- Normal WordPress installs send a descriptive `User-Agent` for server-side
  requests and `Api-User-Agent` for browser-side requests.

WordPress Playground runs PHP HTTP requests through a browser-backed CORS proxy.
That proxy does not allow forwarding the `User-Agent` request header, so the app
detects Playground with `PLAYGROUND_AUTO_LOGIN_AS_USER` and avoids sending that
header there. This is a Playground transport workaround only; normal WordPress
installs still identify requests according to Wikimedia API guidance.
