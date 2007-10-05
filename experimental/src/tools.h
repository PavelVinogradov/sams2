#ifndef SAMS_TOOLS_H
#define SAMS_TOOLS_H

/*!
 * Модуль для различных служебных функций.
 */


#include "defines.h"


/*!
 *  Удаляет комментарии из строки.
 *  Комментарием считается все что следует после символа #
 *  \param str Исходная строка
 *  \return Строку без комментариев
 */
string StripComments(const string str);

/*!
 *  Удаляет из сроки \c str все символы, находящиеся в строке \c needless.
 *  \param str Исходная строка
 *  \param needless Набор символов, подлежащих удалению
 *  \return Строку с удаленными символами
 */
string StripCharacters(const string str, const string needless);

/*!
 *  Удаляет из сроки \c str символы, находящиеся в строке \c needless
 *  до первого символа, не входящего в строку \c needless.
 *  \sa TrimCharactersRight, TrimCharacters
 *  \param str Исходная строка
 *  \param needless Набор символов, подлежащих удалению
 *  \return Строку с удаленными символами
 */
string TrimCharactersLeft(const string str, const string needless);

/*!
 *  Удаляет из сроки \c str символы, находящиеся в строке \c needless
 *  начиная с последнего символа, не входящего в строку \c needless.
 *  \sa TrimCharactersLeft, TrimCharacters
 *  \param str Исходная строка
 *  \param needless Набор символов, подлежащих удалению
 *  \return Строку с удаленными символами
 */
string TrimCharactersRight(const string str, const string needless);

/*!
 *  Удаляет из сроки \c str символы, находящиеся в строке \c needless
 *  до первого и начиная с последнего символа, не входящего в строку \c needless.
 *  \sa TrimCharactersLeft, TrimCharactersRight
 *  \param str Исходная строка
 *  \param needless Набор символов, подлежащих удалению
 *  \return Строку с удаленными символами
 */
string TrimCharacters(const string str, const string needless);

/*!
 *  Удаляет пробельные сиволы из строки слева до первого непробельного символа.
 *  Пробельным символом считается пробел, табуляция, перевод строки.
 *  \sa TrimCharactersLeft, TrimSpacesRight, TrimSpaces
 *  \param str Исходная строка
 *  \return Строку без пробельных символов
 */
string TrimSpacesLeft(const string str);

/*!
 *  Удаляет пробельные сиволы из строки справа, начиная с последнего непробельного символа.
 *  Пробельным символом считается пробел, табуляция, перевод строки.
 *  \sa TrimCharactersRight, TrimSpacesLeft, TrimSpaces
 *  \param str Исходная строка
 *  \return Строку без пробельных символов
 */
string TrimSpacesRight(const string str);

/*!
 *  Удаляет пробельные сиволы из строки слева и справа.
 *  Пробельным символом считается пробел, табуляция, перевод строки.
 *  \sa TrimCharacters, TrimSpacesLeft, TrimSpacesRight
 *  \param str Исходная строка
 *  \return Строку без пробельных символов
 */
string TrimSpaces(const string str);

/*!
 *  Удаляет все пробельные сиволы из строки.
 *  Пробельным символом считается пробел, табуляция, перевод строки.
 *  \sa StripCharacters
 *  \param str Исходная строка
 *  \return Строку без пробельных символов
 */
string StripSpaces(const string str);

#endif /* SAMS_TOOLS_H */
