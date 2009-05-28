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
#ifndef URLGROUP_H
#define URLGROUP_H

#include "config.h"

using namespace std;

#include <string>
#include <vector>

#ifdef USE_PCRECPP
#include <pcrecpp.h>
#else
#  ifdef USE_PCRE
#  include <pcre.h>
#  endif
#endif

/**
 * @brief UrlGroup Группа доступных или запрещенных ресурсов
 */

class UrlGroup
{
public:
  /**
  * @brief Тип доступа к группе ресурсов
  */
  enum accessType
  {
    ACC_DENY,                     ///< Доступ запрещен
    ACC_ALLOW,                    ///< Доступ разрешен
    ACC_REGEXP,                   ///< Доступ запрещен по регулярному выражению
    ACC_REPLACE                   ///< Заменяет url на другой, определенный пользователем
  };

  /**
   * @brief Конструктор
   *
   * @param id Идентификатор группы в БД
   * @param access Тип доступа
   */
  UrlGroup (const long &id, const UrlGroup::accessType &access);

  /**
   * @brief Деструктор
   */
  ~UrlGroup ();

  /**
   * @brief Возвращает идентификатор группы
   *
   * @return Идентификатор группы
   */
  long getId ();

  /**
   * @brief Возвращает тип доступа к группе ресурсов
   *
   * @return Тип доступа к группе ресурсов
   */
  UrlGroup::accessType getAccessType ();

  /**
   * @brief Добавляет определение ресурса в группу
   *
   * @param url Url адрес или регулярное выражение
   */
  void addUrl (const string & url);

  /**
   * @brief Проверяет присутствие ресурса в группе
   *
   * @param url Url адрес
   * @return true если ресурс присутствует и false в противном случае
   */
  bool hasUrl (const string & url) const;

  /**
   * @brief Set destination address
   *
   * @param dest Url адрес to be used instead of original
   */
  void setReplacement (const string & dest);

  string modifyUrl (const string & url) const;

  /**
   * @brief Формирует значения экземпляра класса в виде строки
   *
   * @return Значения экземпляра класса в виде строки
   */
  string asString () const;

  /**
   * @brief Оператор вывода содержимого экземпляра класса в поток
   *
   * @param out Поток вывода
   * @param grp Экземпляр класса
   * @return Поток вывода
   */
  friend ostream & operator<< (ostream & out, const UrlGroup & grp);

protected:
  long _id;                       ///< Идентификатор группы ресурсов
  UrlGroup::accessType _type;     ///< Тип доступа к группе ресурсов
  string _destination;            ///< url Адрес для замены (для _type = ACC_REPLACE)
  vector<string> _list;           ///< Список ресурсов

#ifdef USE_PCRECPP
  vector<pcrecpp::RE*> _patterns; ///< Список скомпилированных регулярных выражений
#else
#  ifdef USE_PCRE
  vector<pcre*> _patterns;        ///< Список скомпилированных регулярных выражений
#  endif
#endif
};

#endif