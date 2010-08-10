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
#ifndef LOCALNETWORKS_H
#define LOCALNETWORKS_H

using namespace std;

#include <string>
#include <vector>

class DBConn;
class Net;

/**
 * @brief Список локальных сетей и доменов
 */
class LocalNetworks
{
public:
  /**
   * @brief Устанавливает использование существующего подключения к БД
   *
   * Метод должен быть использован до вызова reload и load. Иначе будет создано
   * новое подключение к БД.
   *
   * @param conn Существующее подключение к БД
   */
  static void useConnection (DBConn * conn);

  /**
   * @brief Перезагружает список из БД
   *
   * @return true при успешном завершении и false в противном случае
   */
  static bool reload();

  /**
   * @brief Освобождает все ресурсы, выделенные во время работы экземпляра класса
   *
   * Используется для сброса всех переменных в начальное значение
   * без уничтожения экземпляра класса
   */
  static void destroy();

  /**
   * @brief Проверяет является ли @a host локальным
   *
   * @param host Имя или ip адрес хоста
   * @return true если хост является локальным и false в противном случае
   */
  static bool isLocalHost (const string & host);

  /**
   * @brief Проверяет является ли @a url локальным
   *
   * Из url адреса извлекается адрес хоста и проверяется локальность хоста.
   *
   * @param url URL адрес
   * @return true если url является локальным и false в противном случае
   */
  static bool isLocalUrl (const string & url);

  /**
   * @brief Возвращает список всех сетей
   *
   * @return Список сетей
   */
  static vector < Net * > getAllNetworks ();
protected:
  /**
   * @brief Загружает список локальных сетей из БД
   *
   * Если список был загружен ранее, то ничего не происходит.
   *
   * @return true если ошибок нет и false в противном случае
   * @sa reload
   */
  static bool load ();

  static bool _loaded;              ///< Был ли загружен список из БД
  static vector < Net * >_nets;     ///< Список локальных сетей
  static DBConn *_conn;             ///< Используемое подключение к БД
  static bool _connection_owner;    ///< true если владельцем подключения является экземпляр класса
};

#endif
