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
#ifndef TEMPLATELIST_H
#define TEMPLATELIST_H

using namespace std;

#include <map>
#include <vector>
#include <string>

class Template;
class DBConn;

/**
 * @brief Список шаблонов
 */
class TemplateList
{
public:
  /**
   * @brief Перезагружает список из БД
   *
   * @return true при успешном завершении и false в противном случае
   */
  static bool reload();

  /**
   * @brief Устанавливает использование существующего подключения к БД
   *
   * Метод должен быть использован до вызова reload и load. Иначе будет создано
   * новое подключение к БД.
   *
   * @param conn Существующее подключение к БД
   */
  static void useConnection(DBConn *conn);

  /**
   * @brief Освобождает все ресурсы, выделенные во время работы экземпляра класса
   *
   * Используется для сброса всех переменных в начальное значение
   * без уничтожения экземпляра класса
   */
  static void destroy();

  /**
   * @brief Возвращает шаблон по заданному имени
   *
   * @param name Имя шаблона
   * @return Указатель на экземпляр класса или NULL если шаблон с таким именем не найден
   */
  static Template * getTemplate(const string & name);

  /**
   * @brief Возвращает шаблон по заданному идентификатору
   *
   * @param id Идентификатор шаблона
   * @return Указатель на экземпляр класса или NULL если шаблон с таким идентификатором не найден
   */
  static Template * getTemplate(long id);

  /**
   * @brief Возвращает список идентификаторов всех шаблонов
   *
   * @return Список идентификаторов шаблонов
   */
  static vector<long> getIds();

  /**
   * @brief Возвращает список всех шаблонов, сгруппированных по типу авторизации
   *
   * @return Список шаблонов
   */
  static vector<Template*> getList ();

  /**
   * @brief Сохраняет в БД дату следующей очистки счетчиков у шаблонов с нестандартным периодом
   *
   * @return true при успешном завершении и false в противном случае
   */
  static bool saveClearDates ();

private:
  /**
   * @brief Загружает список шаблонов из БД
   *
   * Если список уже был загружен, то ничего не делает.
   *
   * @return true при успешном завершении и false в противном случае
   */
  static bool load();

  static bool _loaded;                  ///< Был ли загружен список из БД
  static map<long, Template*> _list;    ///< Cписок шаблонов
  static DBConn *_conn;                 ///< Используемое подключение к БД
  static bool _connection_owner;        ///< true если владельцем подключения является экземпляр класса
};

#endif
