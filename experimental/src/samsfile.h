#ifndef SAMS_FILE_H
#define SAMS_FILE_H

/*!
 *  Модуль для работы с файлами.
 */

#include <stdio.h>

#include "defines.h"

/*!
 *  Открывает файл с именем fname в режиме fmode.
 *  \retval NULL Если открыть файл не удалось.
 */
FILE *fileOpen (const string fname, const string fmode);

/*!
 *  Закрывает файловый поток \c f.
 */
void fileClose (FILE * f);

/*!
 *  Удаляет файлы с маской \c filemask в директории \c path.
 *  \param path Полное или относительное имя директории.
 *  \param filemask Маска файла. Допустимы символы * и ?.
 *  \retval false Если \c path или \c filemask не определены
 *                или не удалось прочитать каталог \c path
                  или удаление хотя бы одного файла было неудачным.
 */
bool fileDelete (const string path, const string filemask);

#endif /* SAMS_FILE_H */
