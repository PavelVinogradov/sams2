#ifndef SAMS_TOOLS_H
#define SAMS_TOOLS_H

/*!
 * ������ ��� ��������� ��������� �������.
 */


#include "defines.h"


/*!
 *  ������� ����������� �� ������.
 *  ������������ ��������� ��� ��� ������� ����� ������� #
 *  \param str �������� ������
 *  \return ������ ��� ������������
 */
string StripComments(const string str);

/*!
 *  ������� �� ����� \c str ��� �������, ����������� � ������ \c needless.
 *  \param str �������� ������
 *  \param needless ����� ��������, ���������� ��������
 *  \return ������ � ���������� ���������
 */
string StripCharacters(const string str, const string needless);

/*!
 *  ������� �� ����� \c str �������, ����������� � ������ \c needless
 *  �� ������� �������, �� ��������� � ������ \c needless.
 *  \sa TrimCharactersRight, TrimCharacters
 *  \param str �������� ������
 *  \param needless ����� ��������, ���������� ��������
 *  \return ������ � ���������� ���������
 */
string TrimCharactersLeft(const string str, const string needless);

/*!
 *  ������� �� ����� \c str �������, ����������� � ������ \c needless
 *  ������� � ���������� �������, �� ��������� � ������ \c needless.
 *  \sa TrimCharactersLeft, TrimCharacters
 *  \param str �������� ������
 *  \param needless ����� ��������, ���������� ��������
 *  \return ������ � ���������� ���������
 */
string TrimCharactersRight(const string str, const string needless);

/*!
 *  ������� �� ����� \c str �������, ����������� � ������ \c needless
 *  �� ������� � ������� � ���������� �������, �� ��������� � ������ \c needless.
 *  \sa TrimCharactersLeft, TrimCharactersRight
 *  \param str �������� ������
 *  \param needless ����� ��������, ���������� ��������
 *  \return ������ � ���������� ���������
 */
string TrimCharacters(const string str, const string needless);

/*!
 *  ������� ���������� ������ �� ������ ����� �� ������� ������������� �������.
 *  ���������� �������� ��������� ������, ���������, ������� ������.
 *  \sa TrimCharactersLeft, TrimSpacesRight, TrimSpaces
 *  \param str �������� ������
 *  \return ������ ��� ���������� ��������
 */
string TrimSpacesLeft(const string str);

/*!
 *  ������� ���������� ������ �� ������ ������, ������� � ���������� ������������� �������.
 *  ���������� �������� ��������� ������, ���������, ������� ������.
 *  \sa TrimCharactersRight, TrimSpacesLeft, TrimSpaces
 *  \param str �������� ������
 *  \return ������ ��� ���������� ��������
 */
string TrimSpacesRight(const string str);

/*!
 *  ������� ���������� ������ �� ������ ����� � ������.
 *  ���������� �������� ��������� ������, ���������, ������� ������.
 *  \sa TrimCharacters, TrimSpacesLeft, TrimSpacesRight
 *  \param str �������� ������
 *  \return ������ ��� ���������� ��������
 */
string TrimSpaces(const string str);

/*!
 *  ������� ��� ���������� ������ �� ������.
 *  ���������� �������� ��������� ������, ���������, ������� ������.
 *  \sa StripCharacters
 *  \param str �������� ������
 *  \return ������ ��� ���������� ��������
 */
string StripSpaces(const string str);

#endif /* SAMS_TOOLS_H */
