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
#ifndef LOGGER_H
#define LOGGER_H

using namespace std;

#include <string>
#include <fstream>
#include <sys/types.h>

class DBConn;
class DBQuery;

/**
 * @brief Выводит сообщения во время работы программы
 *
 * По умолчанию используется вывод сообщений на терминал.
 */
class Logger
{
public:
  /**
   * @brief Код события.
   */
  enum LogKind
  {
    LK_USER    = 1,            ///< Событие, связанное с пользователями (добавление, удаление, отключение)
    LK_URL     = 2,            ///< Событие, связанное с url (добавление, удаление)
    LK_CACHE   = 3,            ///< Событие, связанное с изменением кеша squid в базе данных
    LK_ADMIN   = 4,            ///< Событие, связанное с администраторами SAMS
    LK_DAEMON  = 10            ///< Событие, связанное с демонами (запуск, остановка, реконфигурирование, ...)
  };

  /**
   * @brief Возвращает текущее время в виде строки ГГГГ-ММ-ДД ЧЧ:ММ:СС
   */
  static string strNow ();

  /**
   * @brief Добавляет информационное сообщение
   *
   * @param mess Информационное сообщение
   */
  static void sendInfo (const string & mess);

  /**
   * @brief Добавляет отладочное сообщение
   *
   * Если текущий уровень отладочных сообщений меньше чем @a level, то сообщение игнорируется.
   *
   * @param level Уровень сообщения
   * @param mess Отладочное сообщение
   */
  static void sendDebug (uint level, const string & mess);

  /**
   * @brief Добавляет предупреждающее сообщение
   *
   * @param mess Предупреждающее сообщение
   */
  static void sendWarning (const string & mess);

  /**
   * @brief Добавляет сообщение об ошибке
   *
   * @param mess Сообщение об ошибке
   */
  static void sendError (const string & mess);

  /**
   * @brief Устанавливает имя отправителя сообщений
   *
   * Как правило, это имя процесса, но ничто не мешает использовать любое другое.
   *
   * @param sender Имя отправителя
   */
  static void setSender(const string & sender);

  /**
   * @brief Устанавливает способ вывода сообщений
   *
   * @a engine может быть указан одним из следующих способов:
   * @li console - выводить сообщения на терминал
   * @li syslog - отправлять сообщения в службу syslog.
   * Используется категория LOG_DAEMON. Уровень сообщений зависит от используемых функций:
   * LOG_INFO для информационных сообщений,
   * LOG_DEBUG для отладочных сообщений,
   * LOG_WARNING для предупреждающих сообщений,
   * LOG_ERR для сообщений об ошибках
   * @li file[:/path/to/file] - выводить сообщения в файл.
   * Если файл существует, то его содержимое предварительно обнуляется.
   * Если путь к файлу не указан, то используется файл samsparser.log в текущей директории.
   *
   * Перед установкой нового потока, старый поток закрывается.
   *
   * @param engine Способ вывода сообщений
   * @return Успешность инициализации
   */
  static bool setEngine (const string & engine);

  /**
   * @brief Устанавливает уровень отладочных сообщений
   *
   * @param level Уровень отладочных сообщений
   */
  static void setDebugLevel (uint level);

  /**
   * @brief Устанавливает режим многословности
   *
   * @param verbose Режим многословности
   */
  static void setVerbose (bool verbose);

  /**
   * @brief Устанавливает использование существующего подключения к БД
   *
   * Метод должен быть использован до вызова addLog. Иначе будет создано
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
   * @brief Добавляет сообщение в БД
   *
   * @param code Код события
   * @param mess Текст сообщения
   */
  static void addLog(LogKind code, const string &mess);

protected:
  /**
   * @brief Закрывает текущий поток вывода сообщений
   */
  static void stop ();

  /**
  * @brief Способ вывода сообщений
  */
  enum LoggerEngine
  {
    OUT_CONSOLE,                ///< Выводить на терминал
    OUT_FILE,                   ///< Выводить в файл
    OUT_SYSLOG                  ///< Отправлять в службу syslog
  };

  static bool _started;                ///< true, Если поток вывода сообщений успешно открыт
  static bool _verbose;                ///< Текущий уровень многословности
  static uint _dbgLevel;               ///< Текущий уровень отладочных сообщений
  static string _sender;               ///< Имя отправителя сообщений
  static LoggerEngine _engine;         ///< Используемый способ вывода сообщений
  static ofstream _fout;               ///< Поток вывода в файл
  static DBConn *_conn;                ///< Используемое подключение к БД
  static bool _connection_owner;       ///< true если владельцем подключения является экземпляр класса
  static pid_t _pid;                   ///< Идентификатор текущего процесса
};

#endif
