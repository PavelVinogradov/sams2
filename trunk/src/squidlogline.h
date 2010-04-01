/***************************************************************************
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 *   This program is distributed in the hope that it will be useful,       *
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of        *
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         *
 *   GNU General Public License for more details.                          *
 *                                                                         *
 *   You should have received a copy of the GNU General Public License     *
 *   along with this program; if not, write to the                         *
 *   Free Software Foundation, Inc.,                                       *
 *   59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.             *
 ***************************************************************************/
#ifndef SQUIDLOGLINE_H
#define SQUIDLOGLINE_H

using namespace std;

#include <string>

#include "ip.h"

/**
 * @brief Строка из файла протокола доступа squid
 */
class SquidLogLine
{
public:

  /**
  *  Note,
  *  TCP_ refers to requests on the HTTP port (3128).
  *  UDP_ refers to requests on the ICP port (3130)
  *  ERR_ refers to various types of errors for HTTP requests.
  *
  */
  enum logCacheResult
  {
    CR_UNKNOWN,                 ///< Unrecognized Cache Result
    TCP_HIT,                    ///< A valid copy of the requested object was in the cache

    /** A valid copy of the requested object was in the cache,
    *  AND it was in memory so it did not have to be read from disk
    */
    TCP_MEM_HIT,

    /** The request was for a negatively-cached object.
    *  Negative-caching refers to caching certain types of errors,
    *  such as "404 Not Found." The amount of time these errors are cached
    *  is controlled with the negative_ttl configuration parameter.
    */
    TCP_NEGATIVE_HIT,
    TCP_MISS,                   ///< The requested object was not in the cache

    /** The object was in the cache, but STALE. An If-Modified-Since request
    *  was made and a "304 Not Modified" reply was received.
    */
    TCP_REFRESH_HIT,
    TCP_REFRESH_MODIFIED,       ///< The requested object was cached but STALE. The IMS query returned the new content.
    TCP_REFRESH_UNMODIFIED,     ///< The requested object was cached but STALE. The IMS query for the object resulted in "304 not modified".

    /** The object was in the cache, but STALE. The request to validate the object
    * failed, so the old (stale) object was returned.
    */
    TCP_REF_FAIL_HIT,

    /** The object was in the cache, but STALE. An If-Modified-Since request was made
    *  and the reply contained new content.
    */
    TCP_REFRESH_MISS,
    TCP_CLIENT_REFRESH,         ///< The client issued a request with the "no-cache" pragma

    TCP_CLIENT_REFRESH_MISS,

    TCP_IMS_HIT,                ///< The client issued an If-Modified-Since request and the object was in the cache and still fresh
    TCP_IMS_MISS,               ///< The client issued an If-Modified-Since request for a stale object
    TCP_SWAPFAIL,               ///< The object was believed to be in the cache, but could not be accessed
    TCP_SWAPFAIL_MISS,          ///<
    TCP_DENIED,                 ///< Access was denied for this request
    UDP_HIT,                    ///< A valid copy of the requested object was in the cache
    UDP_HIT_OBJ,                ///< Same as UDP_HIT
    UDP_MISS,                   ///< The requested object was not in the cache
    UDP_DENIED,                 ///< Access was denied for this request
    UDP_INVALID,                ///< An invalid request was received
    UDP_RELOADING,              ///< The ICP request was "refused" because the cache is busy reloading its metadata
    ERR_CLIENT_ABORT,           ///< The client aborted its request
    ERR_NO_CLIENTS,             ///< There are no clients requesting this URL any more
    ERR_READ_ERROR,             ///< There was a read(2) error while retrieving this object
    ERR_CONNECT_FAIL            ///< Squid failed to connect to the server for this request
  };

  /**
   * @brief Преобразование ответа от squid в строку
   * @param cr Ответ от squid
   * @return Ответ от squid в виде строки
   */
  string toString (logCacheResult cr);

  /** Almost any of these may be preceded by 'TIMEOUT_' if the two-second (default)
  *  timeout occurs waiting for all ICP replies to arrive from neighbors.
  */
  enum logPeerStatus
  {
    PS_UNKNOWN,                 ///< Unrecognized Peer Status
    DIRECT,                     ///< The object has been requested from the origin server
    /** The object has been requested from the origin server because the origin
    *  host IP address is inside your firewall.
    */
    FIREWALL_IP_DIRECT,
    /** The object has been requested from the parent cache
    *  with the fastest weighted round trip time.
    */
    FIRST_PARENT_MISS,
    /** The object has been requested from the first available parent in your list.
    */
    FIRST_UP_PARENT,
    /** The object has been requested from the origin server because the origin
    *  host IP address matched your 'local_ip' list.
    */
    LOCAL_IP_DIRECT,
    /** The object was requested from a sibling cache which replied with a UDP_HIT.
    */
    SIBLING_HIT,
    /** The object could not be requested because of firewall restrictions
    *  and no parent caches were available.
    */
    NO_DIRECT_FAIL,
    /** The object was requested from the origin server because
    *  no parent caches exist for the URL.
    */
    NO_PARENT_DIRECT,
    PARENT_HIT,                 ///< The object was requested from a parent cache which replied with a UDP_HIT
    SINGLE_PARENT,              ///< The object was requested from the only parent cache appropriate for this URL
    SOURCE_FASTEST,             ///< The object was requested from the origin server because the 'source_ping' reply arrived first
    PARENT_UDP_HIT_OBJ,         ///< The object was received in a UDP_HIT_OBJ reply from a parent cache
    SIBLING_UDP_HIT_OBJ,        ///< The object was received in a UDP_HIT_OBJ reply from a sibling cache
    PASSTHROUGH_PARENT,         ///< The neighbor or proxy defined in the config option 'passthrough_proxy' was used
    SSL_PARENT_MISS,            ///< The neighbor or proxy defined in the config option 'ssl_proxy' was used
    /** No ICP queries were sent to any parent caches. This parent was chosen
    *  because it was marked as 'default' in the config file.
    */
    DEFAULT_PARENT,
    /** No ICP queries were received from any parent caches. This parent was chosen
    *  because it was marked as 'default' in the config file and it had
    *  the lowest round-robin use count.
    */
    ROUNDROBIN_PARENT,
    /** This parent was selected because it included the lowest RTT measurement
    *  to the origin server. This only appears with 'query_icmp on' set in the config file.
    */
    CLOSEST_PARENT_MISS,
    /** The object was fetched directly from the origin server because this cache
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

  /**
   * @brief Конструктор
   */
    SquidLogLine ();

  /**
   * @brief Деструктор
   */
   ~SquidLogLine ();

  /**
   * @brief Устанавливает строку из протокола доступа squid
   *
   * @param line Строка из протокола доступа squid
   * @return true если @a line корректна
   */
  bool setLine (const string & line);

  /**
   * @brief Возвращает дату и время когда поступил запрос
   *
   * @return Дата и время когда поступил запрос
   */
  struct tm getTime ();

  /**
   * @brief Возвращает время обработки транзакции
   *
   * @return Время в милисекундах
   */
  int getBusytime ();

  /**
   * @brief Возвращает ip адрес, откуда поступил запрос
   *
   * @return ip адрес, откуда поступил запрос
   */
  string getIP ();

  /**
   * @brief Возвращает строку авторизации
   *
   * @return Строка авторизации
   */
  string getIdent ();

  /**
   * @brief Возвращает url адрес ресурса
   *
   * @return url адрес ресурса
   */
  string getUrl ();

  /**
   * @brief Возвращает тип запроса
   *
   * Тип запроса может быть CONNECT, GET, HEAD, OPTIONS, POST, PROPFIND и т.д.
   *
   * @return Тип запрооса в виде строки
   */
  string getMethod ();

  /**
   * @brief Возвращает размер ресурса в байтах
   *
   * @return Размер ресурса в байтах
   */
  long getSize ();

  /**
   * @brief Возвращает ответ от squid
   *
   * @return Ответ от squid
   */
  logCacheResult getCacheResult ();

  /**
   * @brief Преобразовывает строку в logCacheResult
   *
   * @param cr logCacheResult в виде строки
   * @return logCacheResult
   */
  static logCacheResult parseCacheResult (const string & cr);

  /**
   * @brief Преобразовывает строку в logPeerStatus
   *
   * @param ps logPeerStatus в виде строки
   * @return logPeerStatus
   */
  static logPeerStatus parsePeerStatus (const string & ps);

protected:
  /**
   * @brief Разбор исходной строки из файла протокола squid
   */
  void parseLine ();

  string _line;                 ///< Исходная строка из файла протокола squid
  bool _valid;                  ///< Корректность исходной строки

  struct tm _time;              ///< Дата и время запроса (первая колонка)
  int _busytime;                ///< Время обработки запроса (вторая колонка)
  string _ip;                   ///< IP адрес, откуда поступил запрос (третья колонка)
  logCacheResult _cacheResult;  ///< Тип ответа от squid (четвертая колонка, первое поле)
  long _size;                   ///< Размер объекта (пятая колонка)
  string _method;               ///< Тип запроса (GET, POST, CONNECT,...) (шестая колонка)
  string _url;                  ///< URL адрес объекта (седьмая колонка)
  string _ident;                ///< Имя авторизовавшегося пользователя или IP адрес, откуда поступил запрос (восьмая колонка)
};


#endif
