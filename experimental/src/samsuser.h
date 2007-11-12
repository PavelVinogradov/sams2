#ifndef SAMS_USER_H
#define SAMS_USER_H

#include "defines.h"
#include "samsdb.h"

enum usrAuthType
{
  AUTH_NONE,                    //!< Не используется
  AUTH_NTLM,                    //!< Авторизация в домене Windows через SMB протокол
  AUTH_ADLD,                    //!< Авторизация в домене ActiveDirectory
  AUTH_OPLD,                    //!< Авторизация в OpenLDAP
  AUTH_NCSA,                    //!< Авторизация NCSA
  AUTH_IP                       //!< Авторизация по сетевому (IP) адресу
};

enum usrUseAutoTemplate
{
  TPL_DEFAULT,                  //!< Использовать шаблон по умолчанию
  TPL_SPECIFIED,                //!< Использовать указанный шаблон
  TPL_TAKE_FROM_GROUP           //!< Имя шаблона сопадает с именем первичной группы пользователя
};

enum usrUseAutoGroup
{
  GRP_DEFAULT,                  //!< Использовать группу по умолчанию
  GRP_SPECIFIED,                //!< Использовать указанную группу
  GRP_TAKE_FROM_GROUP           //!< Имя группы сопадает с именем первичной группы пользователя
};

enum usrStatus
{
  STAT_OFF = -1,                //!< Пользователь отключен
  STAT_INACTIVE,                //!< Пользователь превысил лимит
  STAT_ACTIVE                   //!< Пользователь активен
};


string toString (usrAuthType obj);
string toString (usrStatus obj);

class SAMSuser
{
public:
  /*! \brief Конструктор.
   *
   *  Создает экземпляр класса со значениями по умолчанию.
   */
  SAMSuser ();

  /*! \brief Деструктор.
   *
   *  Освобождает все ресурсы, используемые экземпляром класса.
   */
  ~SAMSuser ();

  /*! \brief Устанавливает имя пользователя.
   *
   *  \param name Имя пользователя.
   */
  void setName (const string name);

  /*! \brief Возвращает имя пользователя.
   *
   *  \return Имя пользователя.
   */
  string getName ();

  /*! \brief Устанавливает домен.
   *
   *  \param domain Домен
   */
  void setDomain (const string domain);

  /*! \brief Устанавливает IP адрес и маску.
   *
   *  IP адрес и маска могут быть указаны несколькими способами. Например:
   *  \li 192.168.1.15 255.255.255.255
   *  \li 192.168.1.15 32
   *  \li 192.168.1.15
   *  \n Пустая строка с адресом воспринимается как адрес 0.0.0.0.
   *  \n Пустая строка с маской воспринимается как маска 255.255.255.255.
   *
   *  \param ip IP адрес
   *  \param ipmask Маска
   */
  void setIP (const string ip, const string ipmask);

  /*! \brief Устанавливает флаг активности.
   *
   *  \param enabled Тип активности в виде числа
   */
  void parseEnabled (int enabled);

  /*! \brief Устанавливает флаг активности.
   *
   *  \param enabled Тип активности
   */
  void setEnabled (usrStatus enabled);

  /*! \brief Возвращает флаг активности.
   *
   */
  usrStatus getEnabled ();

  /*! \brief Устанавливает объем израсходованного трафика.
   *
   *  \param size Объем
   */
  void setSize (long size);

  /*! \brief Увеличивает объем израсходованного трафика на \c size.
   *
   *  \param size Объем
   */
  void addSize (long size);

  /*! \brief Возвращает объем израсходованного трафика.
   *
   *  \return Объем израсходованного трафика.
   */
  long getSize ();

  /*! \brief Устанавливает квоту трафика.
   *
   *  \param quote Квота
   */
  void setQuote (long quote);

  /*! \brief Устанавливает идентификатор пользователя
   *
   *  \param id Идентификатор
   */
  void setID (const string id);

  /*! \brief Возвращает идентификатор пользователя
   *
   *  \return Идентификатор
   */
  string getID ();

  /*! \brief Устанавливает объем трафика из кэша.
   *
   *  \param hit Объем
   */
  void setHit (long hit);

  /*! \brief Увеличивает объем трафика из кэша на \c hit.
   *
   *  \param hit Объем
   */
  void addHit (long hit);

  /*! \brief Возвращает объем трафика из кэша.
   *
   *  \return Объем трафика из кэша.
   */
  long getHit ();

  /*! \brief Устанавливает шаблон пользователя
   *
   *  \param shablon Шаблон
   */
  void setShablon (const string shablon);

  /*! \brief Устанавливает тип авторизации.
   *
   *  \param auth Тип авторизации в виде строки
   */
  void parseAuth (const string auth);

  /*! \brief Устанавливает тип авторизации.
   *
   *  \param auth Тип авторизации
   */
  void setAuth (usrAuthType auth);

  /*! \brief Возвращает флаг изменения параметров.
   *
   *  \retval true Параметры были изменены.
   *  \retval false Изменений нет.
   */
  bool hasChanged ();

  /*! \brief Формирует значения класса в виде строки
   *
   */
  string asString ();

protected:
    string _name;               //!< Имя пользователя
  string _domain;               //!< Домен
  int _ip[6];                   //!< IP адрес пользователя
  int _mask[6];                 //!< Маска для IP адреса
  int _octets;                  //!< Количество октетов в IP адресе и маске
  usrStatus _enabled;           //!< Тип активности пользователя
  bool _was_disabled;           //!< Флаг, указывающий что статус пользователя был изменен
  long _size;                   //!< Объем использованного трафика
  long _hit;                    //!< Объем трафика, взятого из кэша
//  double _traffic;
  long _quote;                  //!< Квота
  string _id;                   //!< Идентификатор пользователя
//  string _date;
//  int _updated;
  string _shablon;              //!< Шаблон пользователя
  usrAuthType _auth;            //!< Тип используемой авторизации
  bool _changed;                //!< Флаг, показывающий необходимость записи данных в БД
};

/*! Класс для работы со списком пользователей
 */
class Users
{
public:
  /*! \brief Конструктор.
   *
   *  Создает экземпляр класса со значениями по умолчанию.
   */
  Users ();

  /*! \brief Деструктор.
   *
   *  Освобождает все ресурсы, используемые экземпляром класса.
   */
  ~Users ();

  /*! \brief Считывает пользователей из \с database.
   *
   *  С \с database должно быть установлено соединение.
   *
   *  \param database Источник данных
   */
  bool Read (DB * database);

  /*!
   */
  SAMSuser *findByIdent (const string ident);

  /*!
   */
  void setAutoCreation (bool autocreation);

  /*!
   */
  void setAutoTemplate (usrUseAutoTemplate tplKind, const string tplName);

  /*!
   */
  void setAutoGroup (usrUseAutoGroup grpKind, const string grpName);

  /*! \brief Сохраняет измененные параметры в БД.
   *
   *  \param database Источник данных
   */
  void saveToDB (DB * database);

  /*! \brief Выводит список пользователей в стндартный поток вывода.
   *
   *  Должен быть установлен режим многословности.
   *
   *  \sa dbgSetVerbose()
   */
  void Print ();
protected:
    std::vector < SAMSuser * >_users;
};

#endif /* SAMS_USER_H */
