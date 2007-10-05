#ifndef SAMS_FILE_H
#define SAMS_FILE_H

/*!
 *  ������ ��� ������ � �������.
 */

#include <stdio.h>

#include "defines.h"

/*!
 *  ��������� ���� � ������ fname � ������ fmode.
 *  \retval NULL ���� ������� ���� �� �������.
 */
FILE *fileOpen (const string fname, const string fmode);

/*!
 *  ��������� �������� ����� \c f.
 */
void fileClose (FILE * f);

/*!
 *  ������� ����� � ������ \c filemask � ���������� \c path.
 *  \param path ������ ��� ������������� ��� ����������.
 *  \param filemask ����� �����. ��������� ������� * � ?.
 *  \retval false ���� \c path ��� \c filemask �� ����������
 *                ��� �� ������� ��������� ������� \c path
                  ��� �������� ���� �� ������ ����� ���� ���������.
 */
bool fileDelete (const string path, const string filemask);

#endif /* SAMS_FILE_H */
