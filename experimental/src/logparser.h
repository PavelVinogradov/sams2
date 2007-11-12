#ifndef LOG_PARSER_H
#define LOG_PARSER_H

#include "defines.h"

/*!
 *  Note,
 *  TCP_ refers to requests on the HTTP port (3128).
 *  UDP_ refers to requests on the ICP port (3130)
 *  ERR_ refers to various types of errors for HTTP requests.
 *
 */
enum logCacheResult
{
  /*! Unrecognized Cache Result.
   */
  CR_UNKNOWN,
  /*! A valid copy of the requested object was in the cache.
   */
  TCP_HIT,
  /*! A valid copy of the requested object was in the cache,
   *  AND it was in memory so it did not have to be read from disk.
   */
  TCP_MEM_HIT,
  /*! The request was for a negatively-cached object.
   *  Negative-caching refers to caching certain types of errors,
   *  such as "404 Not Found." The amount of time these errors are cached
   *  is controlled with the negative_ttl configuration parameter.
   */
  TCP_NEGATIVE_HIT,
  /*! The requested object was not in the cache.
   */
  TCP_MISS,
  /*! The object was in the cache, but STALE. An If-Modified-Since request
   *  was made and a "304 Not Modified" reply was received.
   */
  TCP_REFRESH_HIT,
  /*! The object was in the cache, but STALE. The request to validate the object
   * failed, so the old (stale) object was returned.
   */
  TCP_REF_FAIL_HIT,
  /*! The object was in the cache, but STALE. An If-Modified-Since request was made
   *  and the reply contained new content.
   */
  TCP_REFRESH_MISS,
  /*! The client issued a request with the "no-cache" pragma.
   */
  TCP_CLIENT_REFRESH,

  TCP_CLIENT_REFRESH_MISS,
  /*! The client issued an If-Modified-Since request and the object was in the cache and still fresh.
   */
  TCP_IMS_HIT,
  /*! The client issued an If-Modified-Since request for a stale object.
   */
  TCP_IMS_MISS,
  /*! The object was believed to be in the cache, but could not be accessed.
   */
  TCP_SWAPFAIL,
  /*! Access was denied for this request.
   */
  TCP_DENIED,
  /*! A valid copy of the requested object was in the cache.
   */
  UDP_HIT,
  /*! Same as UDP_HIT, but the object data was small enough to be sent in the UDP reply packet.
   *  Saves the following TCP request.
   */
  UDP_HIT_OBJ,
  /*! The requested object was not in the cache.
   */
  UDP_MISS,
  /*! Access was denied for this request.
   */
  UDP_DENIED,
  /*! An invalid request was received.
   */
  UDP_INVALID,
  /*! The ICP request was "refused" because the cache is busy reloading its metadata.
   */
  UDP_RELOADING,
  /*! The client aborted its request.
   */
  ERR_CLIENT_ABORT,
  /*! There are no clients requesting this URL any more.
   */
  ERR_NO_CLIENTS,
  /*! There was a read(2) error while retrieving this object.
   */
  ERR_READ_ERROR,
  /*! Squid failed to connect to the server for this request.
   */
  ERR_CONNECT_FAIL
};

/*! Almost any of these may be preceded by 'TIMEOUT_' if the two-second (default)
 *  timeout occurs waiting for all ICP replies to arrive from neighbors.
 */
enum logPeerStatus
{
  /*! Unrecognized Peer Status.
   */
  PS_UNKNOWN,
  /*! The object has been requested from the origin server.
   */
  DIRECT,
  /*! The object has been requested from the origin server because the origin
   *  host IP address is inside your firewall.
   */
  FIREWALL_IP_DIRECT,
  /*! The object has been requested from the parent cache
   *  with the fastest weighted round trip time.
   */
  FIRST_PARENT_MISS,
  /*! The object has been requested from the first available parent in your list.
   */
  FIRST_UP_PARENT,
  /*! The object has been requested from the origin server because the origin
   *  host IP address matched your 'local_ip' list.
   */
  LOCAL_IP_DIRECT,
  /*! The object was requested from a sibling cache which replied with a UDP_HIT.
   */
  SIBLING_HIT,
  /*! The object could not be requested because of firewall restrictions
   *  and no parent caches were available.
   */
  NO_DIRECT_FAIL,
  /*! The object was requested from the origin server because
   *  no parent caches exist for the URL.
   */
  NO_PARENT_DIRECT,
  /*! The object was requested from a parent cache which replied with a UDP_HIT.
   */
  PARENT_HIT,
  /*! The object was requested from the only parent cache appropriate for this URL.
   */
  SINGLE_PARENT,
  /*! The object was requested from the origin server because the 'source_ping' reply arrived first.
   */
  SOURCE_FASTEST,
  /*! The object was received in a UDP_HIT_OBJ reply from a parent cache.
   */
  PARENT_UDP_HIT_OBJ,
  /*! The object was received in a UDP_HIT_OBJ reply from a sibling cache.
   */
  SIBLING_UDP_HIT_OBJ,
  /*! The neighbor or proxy defined in the config option 'passthrough_proxy' was used.
   */
  PASSTHROUGH_PARENT,
  /*! The neighbor or proxy defined in the config option 'ssl_proxy' was used.
   */
  SSL_PARENT_MISS,
  /*! No ICP queries were sent to any parent caches. This parent was chosen
   *  because it was marked as 'default' in the config file.
   */
  DEFAULT_PARENT,
  /*! No ICP queries were received from any parent caches. This parent was chosen
   *  because it was marked as 'default' in the config file and it had
   *  the lowest round-robin use count.
   */
  ROUNDROBIN_PARENT,
  /*! This parent was selected because it included the lowest RTT measurement
   *  to the origin server. This only appears with 'query_icmp on' set in the config file.
   */
  CLOSEST_PARENT_MISS,
  /*! The object was fetched directly from the origin server because this cache
   *  measured a lower RTT than any of the parent caches.
   */
  CLOSEST_DIRECT
};

/*
enum logHTTPStatus {
100  Continue
101  Switching Protocols
200  OK
201  Created
202  Accepted
203  Non-Authoritative Information
204  No Content
205  Reset Content
206  Partial Content
300  Multiple Choices
301  Moved Permanently
302  Moved Temporarily
303  See Other
304  Not Modified
305  Use Proxy
400  Bad Request
401  Unauthorized
402  Payment Required
403  Forbidden
404  Not Found
405  Method Not Allowed
406  Not Acceptable
407  Proxy Authentication Required
408  Request Time-out
409  Conflict
410  Gone
411  Length Required
412  Precondition Failed
413  Request Entity Too Large
414  Request-URI Too Large
415  Unsupported Media Type
500  Internal Server Error
501  Not Implemented
502  Bad Gateway
503  Service Unavailable
504  Gateway Time-out
505  HTTP Version not supported
};
*/

logCacheResult parseCacheResult (const string cr);
logPeerStatus parsePeerStatus (const string ps);


class SquidLogLine
{
public:
  SquidLogLine ();
  ~SquidLogLine ();

  bool setLine (const string line);
  string getIdent ();
  string getUrl ();
  long getSize ();
  logCacheResult getCacheResult ();
protected:
  void parseLine ();
  string _line;
  bool _valid;

  string _ident;                //!< Authentication server's identification or lookup names of the requesting client.
  string _url;                  //!< URL requested.
  long _size;
  logCacheResult _cacheResult;
};

/*
class SquidLogParser {
public:
  SquidLogParser();
  ~SquidLogParser();

protected:

};
*/
#endif /* #ifndef LOG_PARSER_H */
