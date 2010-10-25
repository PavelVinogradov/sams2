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
#ifndef SAMS_TOOLS_H
#define SAMS_TOOLS_H

/**
 * Модуль для различных служебных функций
 */

using namespace std;

#include <vector>
#include <string>

#define DOMAIN_SEPARATORS "+@\\"

/**
 * @brief Удаляет комментарии из строки
 *
 * Комментарием считается все что следует после символа #
 *
 * @param str Исходная строка
 * @return Строку без комментариев
 */
string StripComments (const string & str);

/**
 * @brief Удаляет символы из строки
 *
 * Удаляет из сроки @a str все символы, находящиеся в строке @a needless
 *
 * @param str Исходная строка
 * @param needless Набор символов, подлежащих удалению
 * @return Строку с удаленными символами
 */
string StripCharacters (const string & str, const string & needless);

/**
 * @brief Удаляет символы из строки
 *
 * Удаляет из сроки @a str символы, находящиеся в строке @a needless
 * до первого символа, не входящего в строку @a needless
 *
 * @sa TrimCharactersRight, TrimCharacters
 * @param str Исходная строка
 * @param needless Набор символов, подлежащих удалению
 * @return Строку с удаленными символами
 */
string TrimCharactersLeft (const string & str, const string & needless);

/**
 * @brief Удаляет символы из строки
 *
 * Удаляет из сроки @a str символы, находящиеся в строке @a needless
 * начиная с последнего символа, не входящего в строку @a needless
 *
 * @sa TrimCharactersLeft, TrimCharacters
 * @param str Исходная строка
 * @param needless Набор символов, подлежащих удалению
 * @return Строку с удаленными символами
 */
string TrimCharactersRight (const string & str, const string & needless);

/**
 * @brief Удаляет символы из строки
 *
 * Удаляет из сроки @a str символы, находящиеся в строке @a needless
 * до первого и начиная с последнего символа, не входящего в строку @a needless
 *
 * @sa TrimCharactersLeft, TrimCharactersRight
 * @param str Исходная строка
 * @param needless Набор символов, подлежащих удалению
 * @return Строку с удаленными символами
 */
string TrimCharacters (const string & str, const string & needless);

/**
 * @brief Удаляет пробельные символы из строки
 *
 * Удаляет пробельные сиволы из строки слева до первого непробельного символа.
 * Пробельным символом считается пробел, табуляция, перевод строки.
 *
 * @sa TrimCharactersLeft, TrimSpacesRight, TrimSpaces
 * @param str Исходная строка
 * @return Строку без пробельных символов
 */
string TrimSpacesLeft (const string & str);

/**
 * @brief Удаляет пробельные символы из строки
 *
 * Удаляет пробельные сиволы из строки справа, начиная с последнего непробельного символа.
 * Пробельным символом считается пробел, табуляция, перевод строки.
 *
 * @sa TrimCharactersRight, TrimSpacesLeft, TrimSpaces
 * @param str Исходная строка
 * @return Строку без пробельных символов
 */
string TrimSpacesRight (const string & str);

/**
 * @brief Удаляет пробельные символы из строки
 *
 * Удаляет пробельные сиволы из строки слева и справа.
 * Пробельным символом считается пробел, табуляция, перевод строки.
 *
 * @sa TrimCharacters, TrimSpacesLeft, TrimSpacesRight
 * @param str Исходная строка
 * @return Строку без пробельных символов
 */
string TrimSpaces (const string & str);

/**
 * @brief Удаляет пробельные символы из строки
 *
 * Удаляет все пробельные сиволы из строки.
 * Пробельным символом считается пробел, табуляция, перевод строки.
 *
 * @sa StripCharacters
 * @param str Исходная строка
 * @return Строку без пробельных символов
 */
string StripSpaces (const string & str);

/**
 * @brief Переводит все символы в нижний регистр
 *
 * @param str Исходная строка
 * @return Строку с символами в нижнем регистре
 */
string ToLower (const string & str);

/**
 * @brief Переводит все символы в верхний регистр
 *
 * @param str Исходная строка
 * @return Строку с символами в верхнем регистре
 */
string ToUpper (const string & str);

/**
 * @brief Проверяет заканчивается ли строка @a str подстрокой @a substr
 *
 * @param str Проверяемая строка
 * @param substr Искомая подстрока
 * @return true если строка @a str заканчивается подстрокой @a substr и false в противном случае
 */
bool endsWith(const string & str, const string & substr);

/**
 * @brief Разбивает одну строку на несколько, используя разделители @a delim
 *
 * @param s Исходная строка
 * @param delim Разделители
 * @param tbl Полученный список
 * @param removeEmpty Включать или нет пустые строки между разделителями
 */
void Split (const string & s, const string & delim, vector < string > &tbl, bool removeEmpty = true);

/**
 * @brief Удаляет файлы с маской @a filemask в директории @a path
 *
 * @param path Полное или относительное имя директории.
 * @param filemask Маска файла. Допустимы символы * и ?.
 * @retval false Если @a path или @a filemask не определены
 *               или не удалось прочитать каталог @a path
 *               или удаление хотя бы одного файла было неудачным.
 */
bool fileDelete (const string & path, const string & filemask);

/**
 * @brief Копирует файл с именем @a name в новый файл с именем @a newname
 *
 * @param name Имя исходного файла
 * @param newname Имя результирующего файла
 * @return true при успешном копировании и false при неудаче.
 */
bool fileCopy (const string & name, const string & newname);

/**
 * @brief Удаляет файл @a path
 *
 * @param path Полное или относительное имя файла
 * @return true если файл удален и false в противном случае
 */
bool fileDelete (const string & path);

/**
 * @brief Проверяет существование файла @a path
 *
 * Файлом может быть как обычный файл, так и специальный
 * (символьная ссылка, директория, фифо, ...).
 * Никаких проверок на права доступа не производится.
 *
 * @param path Полный или относительный путь к файлу
 * @retval false Если @a path не определен или файл не существует или произошла ошибка
 * @retval true Если файл существует
 */
bool fileExist (const string & path);

/**
 * @brief Возвращает список файлов по маске @a filemask в директории @a path
 *
 * @param path Полный или относительный путь к директории
 * @param filemask Маска файла
 */
vector<string> fileList (const string & path, const string & filemask);

/**
 * @brief Отнимает @a days дней от даты @a stime
 */
void timeSubstractDays (struct tm & stime, int days);

/*
 * @brief Шифрует пароль с помощью функции crypt()
 * @param pass Пароль открытым текстом
 * @return Пароль в зашифрованном виде
 */
//string CryptPassword (const string &pass);

/* Returns a url-encoded version of str */
/* IMPORTANT: be sure to free() the returned string after use */
//char *url_encode(const char *str);

/**
 * @brief Возвращает раскодированную строку, которая была закодирована для передачи по url адресу.
 * @param str Закодированая стрка
 * @return Раскодированную строку
 */
string url_decode(const string &str);

#endif /* SAMS_TOOLS_H */
