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

#ifndef DNS_H
#define DNS_H

using namespace std;

#include <string>
#include <vector>
#include <map>

/**
 * @brief Выполненяет преобразование адресов в имена и обратно
 */
class DNS
{
public:
  /**
   * @brief Получает по IP адресу список имен из DNS
   *
   * @param address IP адрес хоста в форме nnn.nnn.nnn.nnn
   * @param names Список имен, зарегистрированных в DNS
   * @retval true Если найдена запись в DNS
   * @retval false Если не найдена запись в DNS или не распознан IP адрес
   */
  static bool getNamesByAddr(const string &address, vector<string> &names);

  /**
   * @brief Получает по доменному имени список IP адресов из DNS
   *
   * @param name Доменное имя хоста
   * @param addrs Список IP адресов, зарегистрированных в DNS
   * @retval true Если найдена запись в DNS
   * @retval false Если не найдена запись в DNS
   */
  static bool getAddrsByName(const string &name, vector<string> &addrs);

protected:
  //static map<string, vector<string> > entries; ///< Кеш предыдущих запросов
};

#endif /*#ifndef DNS_H*/
