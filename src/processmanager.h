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
#ifndef PROCESSMANAGER_H
#define PROCESSMANAGER_H

using namespace std;

#include <string>

/**
 * @brief Управление процессами
 */
class ProcessManager
{
public:
  /**
   * @brief Конструктор
   */
  ProcessManager ();

  /**
   * @brief Деструктор
   */
  ~ProcessManager ();

  /**
   * @brief Запуск текущего процесса
   *
   * Проверяется наличие файла с номером процесса. При его наличии
   * считывается номер процесса из файла и проверяется
   * существование такого процесса. Если искомый процесс не найден
   * то создается файл и в него заносится номер
   * текущего процесса.
   *
   * @param procname Имя процесса
   * @param wait_myself Если обнаружена работающая копия программы, то ждать ее завершения
   * @retval true Текущий процесс запущен успешно
   * @retval false Найден идентичный процесс или произошла ошибка
   */
  bool start (const string & procname, bool wait_myself=false);

  /**
   * @brief Проверяет запущен ли процесс @a procname
   *
   * Проверяется существует ли файл с именем процесса, и если такого нет,
   * то считается что процесс не запущен. Если файл существует, то проверяется
   * существует ли процесс с идентификатором, указанным в этом файле.
   *
   * @param procname Имя проверяемого процесса
   * @return Идентификатор процесса, если он запущен, и 0 если такого процесса нет.
   */
  static pid_t isRunning (const string & procname);

  /**
   * @brief Остановка текущего процесса
   *
   * Удаляется ранее созданный файл с номером текущего процесса
   */
  void stop ();

protected:
  string _fname;              ///< Имя файла где хранится номер процесса
  bool _started;              ///< Статус процесса
};

#endif
