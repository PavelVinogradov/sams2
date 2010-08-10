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
#ifndef SQUIDCONF_H
#define SQUIDCONF_H

#include "config.h"

using namespace std;

#include <fstream>
#include <string>

/**
 * @brief Внесение изменений в конфигурационный файл squid
 */
class SquidConf
{
public:
  /**
   * @brief Конструктор
   */
  SquidConf ();

  /**
   * @brief Деструктор
   */
  ~SquidConf ();

  /**
   * @brief Изменяет конфигурационный файл squid
   *
   * @return true если ошибок не возникло и false в противном случае
   */
  static bool defineAccessRules();

private:
  /**
   * @brief Изменяет конфигурационный файл squid
   *
   * @return true если ошибок не возникло и false в противном случае
   */
  static bool defineACL ();

  /**
   * @brief Пропускает комментарии из потока @a in
   *
   * Все комментарии копируются в поток @a out без изменений.
   * Строки, добавленные SAMS'ом ранее, пропускаются и не копируются в поток @a out
   *
   * @param in Входящий поток
   * @param out Исходящий поток
   * @return Следующую строку, не содержащую комментария
   */
  static string skipComments (ifstream & in, ofstream & out);
};

#endif
