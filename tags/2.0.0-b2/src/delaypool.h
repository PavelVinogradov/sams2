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
#ifndef DELAYPOOL_H
#define DELAYPOOL_H

using namespace std;

#include <string>
#include <map>

/**
 * @brief Пул ограничения скорости
 */
class DelayPool
{
public:

  /**
   * @brief Конструктор
   *
   * @param id Идентификатор пула
   */
  DelayPool (const long & id);

  /**
   * @brief Деструктор
   *
   */
  ~DelayPool ();

  /**
   * @brief Возвращает идентификатор пула
   *
   * @return Идентификатор пула
   */
  long getId () const;

  /**
   * @brief Устанавливает класс пула
   *
   * @return Класс пула
   */
  void setClass (const long &c);

  /**
   * @brief Возвращает класс пула
   *
   * @return Класс пула
   */
  long getClass () const;

  /**
   * @brief Устанавливает параметры
   *
   */
  void setAggregateParams (const long & p1, const long & p2);

  /**
   * @brief Возвращает параметры
   *
   */
  void getAggregateParams (long & p1, long & p2) const;

  /**
   * @brief Устанавливает параметры
   *
   */
  void setNetworkParams (const long & p1, const long & p2);

  /**
   * @brief Возвращает параметры
   *
   */
  void getNetworkParams (long & p1, long & p2) const;

  /**
   * @brief Устанавливает параметры
   *
   */
  void setIndividualParams (const long & p1, const long & p2);

  /**
   * @brief Возвращает параметры
   *
   */
  void getIndividualParams (long & p1, long & p2) const;

  /**
   * @brief Добавляет идентификатор временного ограничения
   *
   * @param id Идентификатор временного ограничения
   */
  void addTimeRange (const long & id, bool negative);

  /**
   * @brief Возвращает список идентификаторов временных границ, когда доступ разрешен
   *
   * @return Список идентификаторов временных границ
   */
  map <long, bool> getTimeRanges () const;

  /**
   * @brief Добавляет идентификатор групы ресурсов
   *
   * @param id Идентификатор группы ресурсов
   */
  void addUrlGroup (const long & id, bool negative);

  /**
   * @brief Возвращает список идентификаторов групп ресурсов
   *
   * @return Список идентификаторов групп ресурсов
   */
  map <long, bool> getUrlGroups () const;

  /**
   * @brief Добавляет идентификатор шаблона
   *
   * @param id Идентификатор шаблона
   */
  void addTemplate (const long & id, bool negative);

  /**
   * @brief Возвращает список идентификаторов шаблонов
   *
   * @return Список идентификаторов шаблонов
   */
  map <long, bool> getTemplates () const;

private:
  long _id;                                ///< Идентификатор пула
  long _class;                             ///< Класс пула
  long _agg1;
  long _agg2;
  long _net1;
  long _net2;
  long _ind1;
  long _ind2;
  map < long, bool > _times;               ///< Список идентификаторов временных границ
  map < long, bool > _urlgroups;           ///< Список групп адресов
  map < long, bool > _templates;           ///< Список шаблонов
};

#endif
