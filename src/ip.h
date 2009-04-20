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
#ifndef IP_H
#define IP_H

using namespace std;

#include <string>
#include <ostream>

#include <arpa/inet.h>

#define IP_ANY 0xfffffffful

/**
 * @brief IP адрес
 */
class IP
{
  friend class Net;
public:
  /**
   * @brief Конструктор
   */
    IP ();

  /**
   * @brief Деструктор
   */
   ~IP ();

  /**
   * @brief Преобразовывает строку в IP адрес
   *
   * IP адрес должен быть представлен в виде a.b.c.d
   *
   * @param str Строковое представление IP адреса
   * @return Экземпляр класса или NULL при ошибке.
   */
  static IP *fromString (const string & str);

  /**
   * @brief Преобразовывает строку в IP адрес
   *
   * IP адрес должен быть представлен в виде a.b.c.d
   *
   * @param str Строковое представление IP адреса
   * @return true при успешном разборе строки и false в противном случае
   */
  bool parseString (const string & str);

  /**
   * @brief Сравнивает два IP адреса
   *
   * @param ip Сравниваемый IP адрес
   * @return true если адреса одинаковы и false в противном случае
   */
  bool equal (const IP & ip);

  /**
   * @brief Формирует значения экземпляра класса в виде строки
   *
   * @return Значения экземпляра класса в виде строки
   */
  string asString () const;

  /**
   * @brief Оператор вывода содержимого экземпляра класса в поток
   * @param out Поток вывода
   * @param ip Экземпляр класса
   * @return Поток вывода
   */
  friend ostream & operator<< (ostream & out, const IP & ip);

protected:
  string _str;                  ///< ip адрес в формате nnn.nnn.nnn.nnn
  struct in_addr _ip;           ///< ip адрес
};


#endif
