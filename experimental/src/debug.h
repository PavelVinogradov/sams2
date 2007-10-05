#ifndef SAMS_DEBUG_H
#define SAMS_DEBUG_H

#include <iostream.h>

//! ��������� ����� ���������� ����������
#define DEBUG0     0

#define DEBUG1     1
#define DEBUG2     2

//! �������� ������� � ��
#define DEBUG3     3
#define DEBUG4     4
#define DEBUG5     5
#define DEBUG6     6
#define DEBUG7     7
#define DEBUG8     8
#define DEBUG9     9

/*!
 *  ��������� ������������ ��� ���������� ��������.
 *  \sa dbgSetLevel
 */
extern uint debug_level;

/*!
 *  ��������� ������������ ��� ���������� ��������.
 *  \sa dbgSetVerbose
 */
extern bool verbose;

/*! ���� ����������, �� � �������� ERROR � WARNING ����� ��������
 *  �������������� �������.
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
 *  ������� ��������� �� ������ � ����������� ����� ������.
 *  \sa ERROR_PREFIX
 */
#define ERROR(arg) \
  { \
    std::cout << ERROR_PREFIX << __FILE__<<"["<<__LINE__<<"] "<<__FUNCTION__<<": " << arg << endl ; \
  }

/*!
 *  ������� ��������� � �������������� � ����������� ����� ������.
 *  \sa WARNING_PREFIX
 */
#define WARNING(arg) \
  { \
    std::cout << WARNING_PREFIX << __FILE__<<"["<<__LINE__<<"] "<<__FUNCTION__<<": " << arg << endl ; \
  }

/*!
 *  ������� ��������� � ����������� ����� ������ ���� ���������� ����� ��������������.
 *  
 *  \sa INFO_PREFIX
 */
#define INFO(arg) \
  if (verbose) { \
    std::cout << INFO_PREFIX << arg << endl ; \
  }

/*!
 *  ������� ���������� ��������� � ����������� ����� ������
 *  ���� ������� ������� ������� ������ ��� \c level.
 *  
 *  \sa debug_level
 *  \sa DEBUG_PREFIX
 */
#define DEBUG(level, arg) \
  if (debug_level > 0 && debug_level >= (level)) { \
    std::cout << DEBUG_PREFIX << __FILE__<<"["<<__LINE__<<"] "<<__FUNCTION__<<": " << arg << endl ; \
  }


/*!
 *  ������������� ������� ������������ ���������� ���������.
 *  \param level ������ �������.
 */
void dbgSetLevel(uint level);

/*!
 *  �������� ����� ��������������.
 *  \param v true, ���� �������� �������������� ���������� � false � ��������� ������.
 */
void dbgSetVerbose(bool v);

#endif /* #ifndef SAMS_DEBUG_H */
