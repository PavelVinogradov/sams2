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
#ifndef PLUGINLIST_H
#define PLUGINLIST_H

using namespace std;

#include <string>
#include <vector>

class DBConn;

/**
 * @brief Информация о плагине
 */
struct Plugin
{
  void *handle;           ///< Обработчик плагина
  string (*getInfo)();    ///< Функция, возвращающая информацию, предоставляемую плагином
  string (*getName)();    ///< Функция, возвращающая название плагина
  string (*getVersion)(); ///< Функция, возвращающая версию плагина
  string (*getAuthor)();  ///< Функция, возвращающая автора плагина
};

/**
 * @brief Список плагинов
 */
class PluginList
{
public:
  /**
   * @brief Перезагружает список из БД
   *
   * @return true при успешном завершении и false в противном случае
   */
  static bool reload ();

  /**
   * @brief Устанавливает использование существующего подключения к БД
   *
   * Метод должен быть использован до вызова reload и load. Иначе будет создано
   * новое подключение к БД.
   *
   * @param conn Существующее подключение к БД
   */
  static void useConnection (DBConn *conn);

  /**
   * @brief Освобождает все ресурсы, выделенные во время работы экземпляра класса
   *
   * Используется для сброса всех переменных в начальное значение
   * без уничтожения экземпляра класса
   */
  static void destroy ();

  /**
   * @brief Обновляет в БД информацию, предоставляемую плагинами
   *
   * Информация обновляется только для активных плагинов.
   *
   * @return true если ошибок нет и false в противном случае
   */
  static bool updateInfo ();
protected:
  /**
   * @brief Загружает и инициализирует плагины
   *
   * Если список был загружен ранее, то ничего не происходит.
   *
   * @return true если ошибок нет и false в противном случае
   * @sa reload
   */
  static bool load ();

  /**
   * @brief Загружает плагин
   *
   * @param path Полный путь к библиотеке плагина
   * @return true если плагин загружен без ошибок и false в противном случае
   */
  static bool loadPlugin (const string &path);

  static bool _loaded;                    ///< Был ли загружен список из БД
  static DBConn *_conn;                   ///< Используемое подключение к БД
  static bool _connection_owner;          ///< true если владельцем подключения является экземпляр класса
  static vector < Plugin * >_plugins;     ///< Список плагинов
};

#endif
