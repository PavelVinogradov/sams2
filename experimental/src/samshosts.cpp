#include <netdb.h>

#include "samshosts.h"
#include "tools.h"
#include "debug.h"

Url::Url ()
{
  _parsed = false;
  DEBUG (DEBUG5, "New instance " << this);
}

Url::~Url ()
{
  DEBUG (DEBUG5, "Destroy instance " << this);
}

void Url::setUrl (const string url)
{
  DEBUG (DEBUG5, "[" << this << "] " << url);
  _url = url;
  _parsed = false;
  _proto = "";
  _user = "";
  _pass = "";
  _addr = "";
  _port = "";
  _path = "";
}

string Url::getProto ()
{
  if (!_parsed)
    parse ();
  return _proto;
}

string Url::getUser ()
{
  if (!_parsed)
    parse ();
  return _user;
}

string Url::getPass ()
{
  if (!_parsed)
    parse ();
  return _pass;
}

string Url::getAddress ()
{
  if (!_parsed)
    parse ();
  return _addr;
}

string Url::getPort ()
{
  if (!_parsed)
    parse ();
  return _port;
}

string Url::getPath ()
{
  if (!_parsed)
    parse ();
  return _path;
}


string Url::asString ()
{
  char s[DEBUG_LINE_SIZE];

  sprintf (&s[0], "Url[%p] %s", this, _url.c_str ());

  return s;
}



void Url::parse ()
{
  int idx_double_slash;
  int idx_at;
  int idx_dot;
  int idx_colon;
  int idx_addr;
  int idx_port;
  int idx_path;
  string line;

  line = _url;

  // На входе строка имеет такой вид:
  // [protocol://][login[@password]:]<canonical.name.dom|ip.address>[:port][/path]

  // Если нашли строку :// то до нее - это протокол.
  // Вытащим его из строки
  idx_double_slash = line.find ("://");
  if (idx_double_slash != -1)
    {
      _proto = line.substr (0, idx_double_slash);
      line.erase (0, idx_double_slash + 3);
    }

  // Из оставшегося все что идет после символа '/' это путь до ресурса.
  // Вытащим его из строки
  idx_path = line.find ('/');
  if (idx_path != -1)
    {
      _path = line.substr (idx_path, line.size () - idx_path);
      line.erase (idx_path, line.size () - idx_path);
    }

  // Оставшаяся строка может иметь такой вид:
  // [login[@password]:]<canonical.name.dom|ip.address>[:port]

  // Определим, указан ли порт
  idx_colon = line.rfind (':');
  if (idx_colon != -1)
    {
      // это может быть указан порт,
      // а может и разделитель пароля и адреса
      _port = line.substr (idx_colon + 1, line.size () - idx_colon - 1);

      // Порт не может быть 0, поэтому если функция atoi вернула 0, то произошла ошибка
      if (atoi (_port.c_str ()) != 0)
        // Все верно, это указан порт
        {
          line.erase (idx_colon, line.size () - idx_colon);
        }
      else
        // не подошло, значит порт здесь не указан
        {
          _port = "";
        }
    }


  // Оставшаяся строка может иметь такой вид:
  // [login[@password]:]<canonical.name.dom|ip.address>

  // Если нашли символ '@' то до него - это логин.
  // Вытащим его из строки
  idx_at = line.find ('@');
  if (idx_at != -1)
    {
      _user = line.substr (0, idx_at);
      line.erase (0, idx_at + 1);
    }

  // Если нашли символ ':' то до него - это пароль или логин.
  idx_colon = line.find (':');

  // обнаружен то-ли пароль, то-ли логин
  // определим что именно и вытащим его из строки
  if (idx_colon != -1)
    {
      if (idx_at == -1)         // логин ранее не встречался, значит он и есть
        {
          _user = line.substr (0, idx_colon);
        }
      else                      // логин уже был, значит это пароль
        {
          _pass = line.substr (0, idx_colon);
        }
      line.erase (0, idx_colon + 1);
    }

  // Все что осталось и есть адрес
  _addr = line;

  _parsed = true;
}








Net::Net ()
{
  _domain = false;
  _resolving = false;
}

Net::~Net ()
{
}

bool Net::setNet (const string net)
{
  DEBUG (DEBUG5, "[" << this << "] " << net);
  _net = net;
  return parse ();
}

/*! \todo Сделать корректное сравнение для IP адресов
 *  \todo Для доменного имени идет проверка по простому вхождению подстроки в строку. Правильно ли это?
 */
bool Net::hasHost (const string host)
{
  bool isname;
  int i;
  int octets;
  int ip[6];
  int mask[6];

  DEBUG (DEBUG5, "Check if " << _net << " contains " << host);

  isname = Net::isDomain (host);

  // Сеть и хост определены доменными именами
  if (_domain && isname)
    {
      DEBUG (DEBUG5, "[" << this << "] " << "domain specifications");
      if (host.find (_net) != -1)
        {
          DEBUG (DEBUG5, "[" << this << "] " << _net << " contains " << host);
          return true;
        }
      else
        {
          return false;
        }
    }
  // Сеть и хост определены адресами
  else if (!isname && !_domain)
    {
      DEBUG (DEBUG5, "[" << this << "] " << "address specifications");
      octets = StringToIP (host, &ip[0], &mask[0]);
      for (i = 0; i < _octets; i++)
        {
          if (_ip[i] != (_mask[i] & ip[i]))
            {
              return false;
            }
        }
      return true;
    }
  // Различный способ указания
  else
    {
      // Если не преобразовывать имена, то ничего сделать не можем
      // потому просто вернем false
      if (!_resolving)
        {
          DEBUG (DEBUG5, "[" << this << "] " << "different specifications, no resolving");
          return false;
        }

      DEBUG (DEBUG5, "[" << this << "] " << "different specifications, need resolving");
      if (!isname)
        {
          octets = StringToIP (host, &ip[0], &mask[0]);
        }
      for (i = 0; i < _octets; i++)
        {
          if (_ip[i] != (_mask[i] & ip[i]))
            {
              return false;
            }
        }
    }
}

void Net::setResolving (bool need_resolv)
{
  _resolving = need_resolv;
  if (_resolving && _domain && !_net.empty ())
    Net::resolve (_net, _octets, &_ip[0]);
}

string Net::asString ()
{
  char s[DEBUG_LINE_SIZE];

  sprintf (&s[0], "Net[%p] %s", this, _net.c_str ());

  return s;
}

bool Net::parse ()
{
  _domain = Net::isDomain (_net);
  if (!_domain)
    _octets = StringToIP (_net, &_ip[0], &_mask[0]);
  else if (_resolving)
    Net::resolve (_net, _octets, &_ip[0]);
}

bool Net::isDomain (const string host)
{
  bool res;
  int i;

  res = true;
  for (i = 0; i < host.size (); i++)
    {
      if (!isdigit ((int) host[i]) && host[i] != '.' && host[i] != '/')
        {
          res = false;
          break;
        }
    }

  DEBUG (DEBUG9, host << " is " << ((res) ? "IP address" : " domain name"));
  return !res;
}

bool Net::resolve (const string host, int &octets, int *ip)
{
  struct hostent *hostinfo;
  int i;

  hostinfo = gethostbyname (host.c_str ());
  if (hostinfo == NULL)
    {
      ERROR ("gethostbyname: " << h_errno);
      return false;
    }
  octets = hostinfo->h_length;
  for (i = 0; i < octets; i++)
    {
      ip[i] = 255 & hostinfo->h_addr[i];
    }
  hostinfo = NULL;
  return true;
}




LocalNets::LocalNets ()
{
}

LocalNets::~LocalNets ()
{
}

bool LocalNets::Read (DB * database)
{
  char s_url[COLUMN_BUFFER_SIZE];
  Net *net;
  DBQuery query (database);

  DEBUG (DEBUG5, "[" << this << "] ");

  if (query.BindCol (1, SQL_C_CHAR, s_url, COLUMN_BUFFER_SIZE) != true)
    {
      return false;
    }
  if (query.SendQueryDirect ("SELECT s_url FROM urls WHERE s_type='local'") != true)
    {
      return false;
    }
  while (query.Fetch () != SQL_NO_DATA)
    {
      net = new Net ();
      net->setNet (s_url);
      _nets.push_back (net);
    }
}

bool LocalNets::isLocal (const string url)
{
  std::vector < Net * >::iterator it;
  string addr;
  Url in;

  in.setUrl (url);
  addr = in.getAddress ();
  for (it = _nets.begin (); it != _nets.end (); it++)
    {
      if ((*it)->hasHost (addr))
        {
          return true;
        }
    }
  return false;
}

void LocalNets::Print ()
{
  std::vector < Net * >::iterator it;
  int idx;
  INFO ("Total " << _nets.size () << " local net[s] available");
  idx = 0;
  for (it = _nets.begin (); it != _nets.end (); it++)
    {
      INFO (std::setw (3) << ++idx << " " << (*it)->asString ());
    }
}
