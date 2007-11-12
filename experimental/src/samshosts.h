#ifndef SAMS_HOSTS_H
#define SAMS_HOSTS_H

#include "defines.h"
#include "samsdb.h"

/*! Класс для работы с url адресом
 *
 */
class Url
{
public:
  /*! \brief Конструктор.
   *
   */
  Url ();

  /*! \brief Деструктор.
   *
   */
  ~Url ();

  /*! \brief Устанавливает url адрес.
   *
   *  Общая допустима форма выглядит следующим образом:
   *  \n [протокол://][логин[@пароль]:]<доменное.имя|ip.адрес>[:порт][/путь/до/ресурса]
   *  Например,
   *  \code
   *  Url myurl;
   *  myurl.setUrl("ftp://mylogin@mypassword:ftp.domain.ru/pub/funny.gif");
   *  myurl.setUrl("http://www.domain.ru:8080");
   *  \endcode
   *  Все установленные ранее параметры сбрасываются.
   *
   *  \param url Url адрес
   */
  void setUrl (const string url);

  /*! \brief Возвращает протокол из url строки.
   *
   *  \return Протокол
   */
  string getProto ();

  /*! \brief Возвращает логин из url строки.
   *
   *  \return Логин
   */
  string getUser ();

  /*! \brief Возвращает пароль из url строки.
   *
   *  \return Пароль
   */
  string getPass ();

  /*! \brief Возвращает адрес из url строки.
   *
   *  \return Доменное имя или IP адрес
   */
  string getAddress ();

  /*! \brief Возвращает порт из url строки.
   *
   *  \return Порт
   */
  string getPort ();

  /*! \brief Возвращает путь к ресурсу из url строки.
   *
   *  \return Путь к ресурсу
   */
  string getPath ();

  /*! \brief Возвращает содержимое экземпляра класса в виде строки.
   *
   *  \return Строку с данными
   */
  string asString ();
protected:
  /*! Обрабатывает исходную url строку, выделяя из нее параметры.
   *
   */
  void parse ();
  string _url;                  //!< Исходный url

  string _proto;                //!< Используемый протокол, например http, ftp
  string _user;                 //!< Имя пользователя для подключения
  string _pass;                 //!< Пароль для подключения
  string _addr;                 //!< Адрес подключения (доменное имя или IP адрес)
  string _port;                 //!< Порт подключения
  string _path;                 //!< Путь до ресурса

  bool _parsed;                 //!< true, если уже функция parse() отработала успешно
  int _ip[6];                   //!< IP адрес
  int _mask[6];                 //!< Маска IP адреса
  int _octets;                  //!< Количество октетов в IP адресе и маске
};

class Net
{
public:
  /*! \brief Конструктор.
   */
  Net ();

  /*! \brief Деструктор.
   */
  ~Net ();

  /*! \brief Устанавливает определение сети.
   *
   *   Сеть может быть определена двумя способами:
   *   \li Доменным именем, напрмер mydomain.com
   *   \li Адресом сети, например 192.168.1.0/24 или 192.168.1.0/255.255.255.0
   *
   *   \param net Определение сети
   *   \retval true Сеть определена корректно
   *   \retval false Неверное определение сети
   */
  bool setNet (const string net);

  /*! \brief Определяет входит ли хост \c host в сеть.
   *
   *   Если способ указания сети не совпадает со способом указания хоста,
   *   то должно быть включено преобразование имен,
   *   иначе функция будет возвращать false. Если и сеть и хост указаны доменными именами,
   *   то вхождение хоста определяется простым нахождением подстроки (хоста) в строке (сети).
   *   Если же сеть и хост указаны адресами, то сравниваются биты, указанные в маске сети.
   *
   *   \param host Интересующий хост.
   *   \retval true Если хост входит в заданную сеть.
   *   \retval false Если хост не входит в заданную сеть.
   *   \sa setResolving()
   */
  bool hasHost (const string host);

  /*! \brief Меняет режим преобразования имен.
   *
   *   Имена преобразовываются только если способ указания сети и хоста не совпадают.
   *
   *   \param need_resolv true, если необходимо преобразование имен.
   */
  void setResolving (bool need_resolv);

  /*! \brief Определяет тип указания хоста
   *
   *   \retval true Если хост указан доменным именем.
   *   \retval false Если хост указан IP адресом.
   */
  static bool isDomain (const string host);

  /*! \brief Возвращает содержимое экземпляра класса в виде строки.
   *
   *  \return Строку с данными
   */
  string asString ();
protected:
  /*!  Определяет параметры сети.
   */
    bool parse ();

  /*!  Определяет адрес по доменному имени.
   */
  static bool resolve (const string host, int &octets, int *ip);

  string _net;                  //!< Исходное определение группы хостов
  bool _resolving;              //!< Режим преобразования имен
  bool _domain;                 //!< Тип указания сети (true-доменным именем, false-IP адресом)
  int _ip[6];                   //!< Адрес сети
  int _mask[6];                 //!< Маска сети
  int _octets;                  //!< Количество октетов в адресе и маске сети
};

class LocalNets
{
public:
  LocalNets ();
  ~LocalNets ();
  bool Read (DB * database);
  bool isLocal (const string host);
  void Print ();
protected:
    std::vector < Net * >_nets;
};

#endif /* SAMS_HOSTS_H */
