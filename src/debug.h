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
#ifndef SAMS_DEBUG_H
#define SAMS_DEBUG_H

using namespace std;

#include <iostream>
#include <sstream>
#include <iomanip>

#include "logger.h"

#define ERR_OK            0
#define ATTR_NOT_FOUND    1
#define ATTR_NOT_PARSED   2




#define DEBUG0     0            ///< Отключить вывод отладочной информации
#define DEBUG1     1            ///< Сообщения, попадающие в БД
#define DEBUG2     2            ///<
#define DEBUG3     3            ///< Используемые настройки
#define DEBUG4     4            ///<
#define DEBUG5     5            ///< Результат поиска в списках
#define DEBUG6     6            ///< Запросы к БД и управление подключениями
#define DEBUG7     7            ///< Работа конструкторов и деструкторов
#define DEBUG8     8            ///< Установка/запрос атрибутов объекта
#define DEBUG9     9            ///< Вспомогательные функции и большие циклы

#define DEBUG_CMDARG  DEBUG2
#define DEBUG_PROXY   DEBUG2
#define DEBUG_DAEMON  DEBUG2
#define DEBUG_DB      DEBUG3
#define DEBUG_REDIR   DEBUG4
#define DEBUG_USER    DEBUG4
#define DEBUG_URL     DEBUG5
#define DEBUG_HOST    DEBUG5
#define DEBUG_PARSER  DEBUG6
#define DEBUG_GROUP   DEBUG6
#define DEBUG_TPL     DEBUG6
#define DEBUG_LOGGER  DEBUG7
#define DEBUG_FILE    DEBUG8


/** Если определена, то в макросах ERROR и WARNING будет выводится
 *  дополнительный префикс.
 */
#define DISPLAY_DEBUG_PREFIX 1


#ifdef DISPLAY_DEBUG_PREFIX
#define ERROR_PREFIX    "***ERROR: "
#define WARNING_PREFIX  "+++WARNING: "
#define INFO_PREFIX     ""
#define DEBUG_PREFIX    ""
#else
#define ERROR_PREFIX    ""
#define WARNING_PREFIX  ""
#define INFO_PREFIX     ""
#define DEBUG_PREFIX    ""
#endif


/**
 * @brief Выводит сообщение об ошибке
 *
 * @sa ERROR_PREFIX, Logger
 */
#define ERROR(arg) \
  { \
    basic_stringstream < char >s; \
    s << ERROR_PREFIX << __FILE__<<":"<<__LINE__<< " " << arg; \
    Logger::sendError(s.str()); \
  }


/**
 * @brief Выводит сообщение о предупреждении
 *
 * @sa WARNING_PREFIX, Logger
 */
#define WARNING(arg) \
  { \
    basic_stringstream < char >s; \
    s << WARNING_PREFIX << arg; \
    Logger::sendWarning(s.str()); \
  }


/**
 * @brief Выводит сообщение если установлен режим многословности
 *
 * @sa INFO_PREFIX, Logger
 */
#define INFO(arg) \
  { \
    basic_stringstream < char >s; \
    s << INFO_PREFIX << arg; \
    Logger::sendInfo(s.str()); \
  }


/**
 *  Выводит отладочное сообщение
 *  если текущий уровень отладки больше чем @a level
 *
 *  @sa DEBUG_PREFIX, Logger
 */
#define DEBUG(level, arg) \
  { \
    basic_stringstream < char >s; \
    s << DEBUG_PREFIX << __FILE__<<":"<<__LINE__<< " " << arg; \
    Logger::sendDebug(level, s.str()); \
  }


#endif /* #ifndef SAMS_DEBUG_H */
