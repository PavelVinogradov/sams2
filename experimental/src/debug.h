#ifndef SAMS_DEBUG_H
#define SAMS_DEBUG_H

#include <iostream.h>

//! Отключить вывод отладочной информации
#define DEBUG0     0

#define DEBUG1     1
#define DEBUG2     2

//! Выводить запросы к БД
#define DEBUG3     3
#define DEBUG4     4
#define DEBUG5     5
#define DEBUG6     6
#define DEBUG7     7
#define DEBUG8     8
#define DEBUG9     9

/*!
 *  Избегайте использовать эту переменную напрямую.
 *  \sa dbgSetLevel
 */
extern uint debug_level;

/*!
 *  Избегайте использовать эту переменную напрямую.
 *  \sa dbgSetVerbose
 */
extern bool verbose;

/*! Если определена, то в макросах ERROR и WARNING будет выодится
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

/*!
 *  Выводит сообщение об ошибке в стандартный поток вывода.
 *  \sa ERROR_PREFIX
 */
#define ERROR(arg) \
  { \
    std::cout << ERROR_PREFIX << __FILE__<<"["<<__LINE__<<"] "<<__FUNCTION__<<": " << arg << endl ; \
  }

/*!
 *  Выводит сообщение о предупреждении в стандартный поток вывода.
 *  \sa WARNING_PREFIX
 */
#define WARNING(arg) \
  { \
    std::cout << WARNING_PREFIX << __FILE__<<"["<<__LINE__<<"] "<<__FUNCTION__<<": " << arg << endl ; \
  }

/*!
 *  Выводит сообщение в стандартный поток вывода если установлен режим многословности.
 *  
 *  \sa INFO_PREFIX
 */
#define INFO(arg) \
  if (verbose) { \
    std::cout << INFO_PREFIX << arg << endl ; \
  }

/*!
 *  Выводит отладочное сообщение в стандартный поток вывода
 *  если текущий уровень отладки больше чем \c level.
 *  
 *  \sa debug_level
 *  \sa DEBUG_PREFIX
 */
#define DEBUG(level, arg) \
  if (debug_level > 0 && debug_level >= (level)) { \
    std::cout << DEBUG_PREFIX << __FILE__<<"["<<__LINE__<<"] "<<__FUNCTION__<<": " << arg << endl ; \
  }


/*!
 *  Устанавливает уровень показываемых отладочных сообщений.
 *  \param level Уровнь отладки.
 */
void dbgSetLevel(uint level);

/*!
 *  Изменяет режим многословности.
 *  \param v true, если выводить дополнительную информацию и false в противном случае.
 */
void dbgSetVerbose(bool v);

#endif /* #ifndef SAMS_DEBUG_H */
