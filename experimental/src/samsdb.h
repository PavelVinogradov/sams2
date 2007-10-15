#ifndef SAMS_DB_H
#define SAMS_DB_H

/*
 * Модуль предназначен для работы с СУБД через ODBC
 *
 *
 */

#include <strings.h>
#include <sql.h>
#include <sqlext.h>
#include <sqltypes.h>

#include "defines.h"

class DB {
public:

  /*! Конструктор класса.
   *
   */
  DB();

  /*! Деструктор класса. Самостоятельно закрывает соединение,
   *  если оно было установлено, и освобождает все используемые ресурсы.
   *
   */
  ~DB();

  /*! Подключается к \c datasource используя \c username и \c password.
   *  Если данный источник настроен с использованием имени пользователя
   *  и пароля по умолчанию, то их можно указать пустыми.
   *
   *  \param datasource Источник данных, как настроен в ODBC.
   *  \param username Имя пользователя.
   *  \param password Пароль.
   *  \retval true Если подключение прошло успешно.
   *  \retval false При ошибке.
   */
  bool Connect(const string datasource, const string username, const string password);

  /*! Возвращает состояние соединения.
   *
   *  \retval true Соединение установлено.
   *  \retval false Соединение отсутствует.
   */
  bool isConnected();

  /*! Закрывает соединение с источником данных и освобождает все используемые ресурсы.
   *
   */
  void Disconnect();

  /*! Добавляет колонку в результат запроса. Используется с запросом типа SELECT.
   *
   *  \param colNum Номер колонки, начиная с 1.
   *  \param dstType Тип параметра. Наиболее часто используемые значения:
   *         SQL_C_CHAR, SQL_C_LONG, SQL_C_SHORT, SQL_C_FLOAT, SQL_C_DOUBLE,
   *         SQL_C_NUMERIC, SQL_C_DATE, SQL_C_TIME, SQL_C_TIMESTAMP,
   *         SQL_C_BINARY, SQL_C_BIT, SQL_C_SBIGINT, SQL_C_UBIGINT,
   *         SQL_C_TINYINT, SQL_C_ULONG, SQL_C_USHORT, SQL_C_UTINYINT,
   *  \param dstValue Адрес переменной, куда помещается значение.
   *  \param dstLength Длина переменной.
   *  \retval true При успешном добавлении колонки.
   *  \retval false При возникновении ошибки.
   *  \sa SendQuery, Fetch
   */
  bool AddCol(SQLUSMALLINT colNum, SQLSMALLINT dstType, SQLPOINTER dstValue, SQLLEN dstLength);

  /*! Выполняет запрос \c query.
   *
   *  \param query SQL запрос.
   *  \retval true Запрос выполнен успешно.
   *  \retval false Произошла ошибка при выполнении запроса.
   *  \sa AddCol, Fetch
   */
  bool SendQuery(const string query);

  /*! Выбирает следующую строку данных из набора результатов
   *  и возвращает данные для всех связанных столбцов
   *  \retval SQL_NO_DATA Если в наборе результатов не осталось строк с данными.
   */
  SQLRETURN Fetch();

  /*! Возвращает количество строк, на которые воздействует инструкция UPDATE, INSERT или DELETE.
   *
   */
  SQLINTEGER RowsCount();

protected:

  /*! Сбрасывает состояние класса в первоначальное значение без освобождения ресурсов.
   *
   */
  void     reset();

  /*! Возвращает сообщение о последней ошибке.
   *
   * \param handleType Тип идентификатора.
   *        Возможные значения: SQL_HANDLE_ENV, SQL_HANDLE_DBC, SQL_HANDLE_STMT, SQL_HANDLE_DESC
   * \param handle Указатель на идентификатор.
   * \return Сообщение об ошибке или пустую строку.
   */
  string   getErrorMessage(SQLSMALLINT handleType, SQLHANDLE handle);

  //! Guess yourself
  bool     connected;
  //! ODBC Datasource
  string   source;
  //! Username to use for connection
  string   user;
  //! Password to use for connection
  string   pass;
  //! Handle for environment
  SQLHENV  env;
  //! Handle for a connection
  SQLHDBC  conn;
  //! Handle for a statement
  SQLHSTMT statement;
};


#endif /* #ifndef SAMS_DB_H */
