#ifndef SAMS_TOOLS_H
#define SAMS_TOOLS_H

/*!
 * Модуль для различных служебных функций.
 */


#include "defines.h"


/*! \brief Удаляет комментарии из строки.
 *
 *  Комментарием считается все что следует после символа #
 *
 *  \param str Исходная строка
 *  \return Строку без комментариев
 */
string StripComments (const string str);

/*! \brief Удаляет символы из строки.
 *
 *  Удаляет из сроки \c str все символы, находящиеся в строке \c needless.
 *
 *  \param str Исходная строка
 *  \param needless Набор символов, подлежащих удалению
 *  \return Строку с удаленными символами
 */
string StripCharacters (const string str, const string needless);

/*! \brief Удаляет символы из строки.
 *
 *  Удаляет из сроки \c str символы, находящиеся в строке \c needless
 *  до первого символа, не входящего в строку \c needless.
 *
 *  \sa TrimCharactersRight, TrimCharacters
 *  \param str Исходная строка
 *  \param needless Набор символов, подлежащих удалению
 *  \return Строку с удаленными символами
 */
string TrimCharactersLeft (const string str, const string needless);

/*! \brief Удаляет символы из строки.
 *
 *  Удаляет из сроки \c str символы, находящиеся в строке \c needless
 *  начиная с последнего символа, не входящего в строку \c needless.
 *
 *  \sa TrimCharactersLeft, TrimCharacters
 *  \param str Исходная строка
 *  \param needless Набор символов, подлежащих удалению
 *  \return Строку с удаленными символами
 */
string TrimCharactersRight (const string str, const string needless);

/*! \brief Удаляет символы из строки.
 *
 *  Удаляет из сроки \c str символы, находящиеся в строке \c needless
 *  до первого и начиная с последнего символа, не входящего в строку \c needless.
 *
 *  \sa TrimCharactersLeft, TrimCharactersRight
 *  \param str Исходная строка
 *  \param needless Набор символов, подлежащих удалению
 *  \return Строку с удаленными символами
 */
string TrimCharacters (const string str, const string needless);

/*! \brief Удаляет пробельные символы из строки.
 *
 *  Удаляет пробельные сиволы из строки слева до первого непробельного символа.
 *  Пробельным символом считается пробел, табуляция, перевод строки.
 *
 *  \sa TrimCharactersLeft, TrimSpacesRight, TrimSpaces
 *  \param str Исходная строка
 *  \return Строку без пробельных символов
 */
string TrimSpacesLeft (const string str);

/*! \brief Удаляет пробельные символы из строки.
 *
 *  Удаляет пробельные сиволы из строки справа, начиная с последнего непробельного символа.
 *  Пробельным символом считается пробел, табуляция, перевод строки.
 *
 *  \sa TrimCharactersRight, TrimSpacesLeft, TrimSpaces
 *  \param str Исходная строка
 *  \return Строку без пробельных символов
 */
string TrimSpacesRight (const string str);

/*! \brief Удаляет пробельные символы из строки.
 *
 *  Удаляет пробельные сиволы из строки слева и справа.
 *  Пробельным символом считается пробел, табуляция, перевод строки.
 *
 *  \sa TrimCharacters, TrimSpacesLeft, TrimSpacesRight
 *  \param str Исходная строка
 *  \return Строку без пробельных символов
 */
string TrimSpaces (const string str);

/*! \brief Удаляет пробельные символы из строки.
 *
 *  Удаляет все пробельные сиволы из строки.
 *  Пробельным символом считается пробел, табуляция, перевод строки.
 *
 *  \sa StripCharacters
 *  \param str Исходная строка
 *  \return Строку без пробельных символов
 */
string StripSpaces (const string str);

/*! \brief Преобразовывает IP адрес из строки в последовательность чисел.
 *
 *  IP адрес может быть представлен в следующих нотациях:
 *  \li <B>ip/mask</B>, например, 192.168.1.15/255.255.255.255
 *  \li <B>ip/bits</B>, например, 192.168.1.15/32
 *  \li <B>ip</B>, например, 192.168.1.15, маска воспринимается как 255.255.255.255
 *  \li <B>/mask</B>, например, /255.255.255.255, IP адрес воспринимается как 0.0.0.0
 *  \li <B>/bits</B>, например, /32, IP адрес воспринимается как 0.0.0.0
 *
 *  \param url Строковое представление IP адреса
 *  \param ip Числовое представление IP адреса (указатель на массив из 6 чисел)
 *  \param mask Числовое представление маски IP адрес (указатель на массив из 6 чисел)
 *  \return Количество октетов (0 при ошибке, 4 для IPv4, 6 для IPv6)
 */
int StringToIP (const string url, int *ip, int *mask);

/*! \brief Преобразовывает последовательноть чисел IP адреса в строку.
 *
 *  \param ip IP адрес в виде чисел
 *  \param mask Маска IP адреса в виде чисел
 *  \param octets Количество используемых октетов
 *  \return IP адрес в виде строки
 */
string IPToString (int ip[6], int mask[6], int octets);

/*! \brief Разбивает одну строку на несколько, используя разделитель \c delim.
 *
 *  \param s Исходная строка
 *  \param delim Разделитель
 *  \param tbl Полученный список
 */
void Split (const string s, const string delim, std::vector < string > &tbl);

#endif /* SAMS_TOOLS_H */
