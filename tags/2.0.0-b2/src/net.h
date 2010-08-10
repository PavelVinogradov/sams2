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
#ifndef NET_H
#define NET_H

using namespace std;

#include <string>
#include "ip.h"

/**
 * @brief Группа хостов (домен, подсеть)
 */
class Net
{
public:
  /**
   * @brief Конструктор
   */
  Net ();

  /**
   * @brief Деструктор
   */
  ~Net ();

  void setId(long id);

  long getId() const;

  /* @brief Устанавливает определение сети.
   *
   *  Сеть может быть определена двумя способами:
   *  @li Доменным именем, напрмер mydomain.com
   *  @li Адресом сети, например 192.168.1.0/24 или 192.168.1.0/255.255.255.0
   *
   *  @param net Определение сети
   *  @retval true Сеть определена корректно
   *  @retval false Неверное определение сети
   */
//  bool setNet (const string &net);

  /** @brief Определяет входит ли хост @a host в сеть.
   *
   *  Если способ указания сети не совпадает со способом указания хоста,
   *  то должно быть включено преобразование имен,
   *  иначе функция будет возвращать false. Если и сеть и хост указаны доменными именами,
   *  то вхождение хоста определяется простым нахождением подстроки (сети) в строке (хоста).
   *  Если же сеть и хост указаны адресами, то сравниваются биты, указанные в маске сети.
   *
   *  @param host Интересующий хост.
   *  @retval true Если хост входит в заданную сеть.
   *  @retval false Если хост не входит в заданную сеть.
   *
   *  @sa Proxy::isUseDNS
   */
  bool hasHost (const string & host);

  /**
   * @brief Проверяет входит ли IP адрес @a ip в данную сеть
   *
   * @param ip IP адрес
   * @return true если ip адрес принадлежит сети
   */
  bool hasIP (const IP & ip);

  /** @brief Определяет тип указания хоста
   *
   *  @param host Определение хоста
   *  @retval true Если хост определен доменным именем
   *  @retval false Если хост определен ip адресом
   */
  static bool isDomain (const string & host);

  /** @brief Определяет тип указания сети
   *
   *  @retval true Если сеть определена доменным именем
   *  @retval false Если сеть определена ip адресом с маской подсети
   */
  bool isDomain ();

  /** @brief Возвращает содержимое экземпляра класса в виде строки
   *
   *  @return Строку с данными
   */
  string asString ();

  /**
   * @brief Преобразовывает строку в адрес сети
   *
   * Адрес сети должен быть представлен в виде a.b.c.d/e или a.b.c.d/a.b.c.d
   *
   * @param str
   * @return Экземпляр класса
   */
  static Net *fromString (const string & str);

protected:
  long _id;                     ///< Идентификатор сети
  string _net;                  ///< Исходное определение сети
  bool _domain;                 ///< Тип указания сети (true-доменным именем, false-IP адресом)
  IP *_ip;                      ///< Адрес сети
  struct in_addr _mask;         ///< Маска сети
};

#endif
