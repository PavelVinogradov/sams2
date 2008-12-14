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
#ifndef URL_H
#define URL_H

using namespace std;

#include <string>

/**
 * @brief Url адрес
 */

class Url
{
public:
  /**
   * @brief Конструктор
   */
  Url ();

  /**
   * @brief Деструктор
   */
  ~Url ();

  /**
   * @brief Устанавливает url адрес
   *
   * Общий формат выглядит следующим образом:
   * @n [протокол://][логин[@пароль]:]<доменное.имя|ip.адрес>[:порт][/путь/до/ресурса|?запрос]
   * Например,
   * @code
   * Url myurl;
   * myurl.setUrl("ftp://mylogin@mypassword:ftp.domain.ru/pub/funny.gif");
   * myurl.setUrl("http://www.domain.ru:8080");
   * @endcode
   * Все установленные ранее параметры сбрасываются.
   *
   * @param url Url адрес
   */
  void setUrl (const string & url);

  /**
   * @brief Возвращает протокол из url строки
   *
   * @return Протокол
   */
  string getProto ();

  /**
   * @brief Возвращает логин из url строки
   *
   * @return Логин
   */
  string getUser ();

  /**
   * @brief Возвращает пароль из url строки
   *
   * @return Пароль
   */
  string getPass ();

  /**
   * @brief Возвращает адрес из url строки
   *
   * @return Доменное имя или IP адрес
   */
  string getAddress ();

  /**
   * @brief Возвращает порт из url строки
   *
   * @return Порт
   */
  string getPort ();

  /**
   * @brief Возвращает путь к ресурсу из url строки
   *
   * @return Путь к ресурсу
   */
  string getPath ();

  /**
   * @brief Возвращает содержимое экземпляра класса в виде строки
   *
   * @return Строку с данными
   */
  string asString () const;

  /**
   * @brief Создает экземпляр класса с URL адресом @a url
   *
   * @param url URL адрес
   * @return Экземпляр класса
   */
  static Url *fromString (const string & url);

  /**
   * @brief Оператор вывода содержимого экземпляра класса в поток
   * @param out Поток вывода
   * @param url Экземпляр класса
   * @return Поток вывода
   */
  friend ostream & operator<< (ostream & out, const Url & url);

protected:
  /**
   * @brief Обрабатывает исходную url строку, выделяя из нее параметры.
   */
  void parse ();

  string _url;                  ///< Исходный url
  string _proto;                ///< Используемый протокол, например http, ftp
  string _user;                 ///< Имя пользователя для подключения
  string _pass;                 ///< Пароль для подключения
  string _addr;                 ///< Адрес подключения (доменное имя или IP адрес)
  string _port;                 ///< Порт подключения
  string _path;                 ///< Путь до ресурса
};

#endif
