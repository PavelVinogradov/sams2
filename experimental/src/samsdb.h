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

#define COLUMN_BUFFER_SIZE 256

class DB
{
  friend class DBQuery;
public:

  /*! Конструктор класса.
   *
   */
    DB ();

  /*! Деструктор класса. Самостоятельно закрывает соединение,
   *  если оно было установлено, и освобождает все используемые ресурсы.
   *
   */
   ~DB ();

  /*! Подключается к \c datasource используя \c username и \c password.
   *  Если данный источник настроен с использованием имени пользователя
   *  и пароля по умолчанию, то их можно указать пустыми.
   *
   *  \code
   *  DB mydb;
   *  mydb.Connect("datasource", "user", "password");
   *  mydb.Disconnect();
   *  \endcode
   *
   *  \param datasource Источник данных, как настроен в ODBC.
   *  \param username Имя пользователя.
   *  \param password Пароль.
   *  \retval true Если подключение прошло успешно.
   *  \retval false При ошибке.
   */
  bool Connect (const string datasource, const string username, const string password);

  /*! Возвращает состояние соединения.
   *
   *  \retval true Соединение установлено.
   *  \retval false Соединение отсутствует.
   */
  bool isConnected ();

  /*! Закрывает соединение с источником данных и освобождает все используемые ресурсы.
   *
   */
  void Disconnect ();

  /*! Возвращает сообщение о последней ошибке.
   *
   * \param handleType Тип идентификатора.
   *        Возможные значения: SQL_HANDLE_ENV, SQL_HANDLE_DBC, SQL_HANDLE_STMT, SQL_HANDLE_DESC
   * \param handle Указатель на идентификатор.
   * \return Сообщение об ошибке или пустую строку.
   */
  static string getErrorMessage (SQLSMALLINT handleType, SQLHANDLE handle);

private:
  //! Handle for a connection
    SQLHDBC conn;
  //! Guess yourself
  bool connected;

protected:

  /*! Сбрасывает состояние класса в первоначальное значение без освобождения ресурсов.
   *
   */
  void reset ();

  //! ODBC Datasource
  string source;
  //! Username to use for connection
  string user;
  //! Password to use for connection
  string pass;
  //! Handle for environment
  SQLHENV env;
};


class DBQuery
{
public:
  /*! Конструктор класса.
   *
   */
  DBQuery (DB * database);

  /*! Деструктор класса.
   *
   */
  ~DBQuery ();

  /*! \brief Привязывает буферы данных прикладных программ к столбцам в наборе результатов.
   *
   *  Например, следующий код используется для печати значений таблицы:
   *  \code
   *  long num;
   *  char desc[50];
   *  DBQuery query(mydb);
   *  query.BindCol( 1, SQL_C_LONG, &num,     10);
   *  query.BindCol( 2, SQL_C_CHAR, &desc[0], 50);
   *  query.SendQuery("SELECT id, name from my_test");
   *  while (query.Fetch() != SQL_NO_DATA)
   *  {
   *    printf("id=%d, name=%s\n", num, desc);
   *  }
   *  \endcode
   *
   *  \param colNum Номер параметра, упорядоченный последовательно
   *         в увеличивающемся порядке, начинающемся с 1.
   *  \param dstType Тип данных для C параметра. Наиболее часто используемые значения:
   *         SQL_C_CHAR, SQL_C_LONG, SQL_C_SHORT, SQL_C_FLOAT, SQL_C_DOUBLE,
   *         SQL_C_NUMERIC, SQL_C_DATE, SQL_C_TIME, SQL_C_TIMESTAMP,
   *         SQL_C_BINARY, SQL_C_BIT, SQL_C_SBIGINT, SQL_C_UBIGINT,
   *         SQL_C_TINYINT, SQL_C_ULONG, SQL_C_USHORT, SQL_C_UTINYINT
   *  \param dstValue Указатель на буфер для данных параметра.
   *  \param dstLength Длина буфера \c dstValue в байтах.
   *  \retval true При успешном добавлении колонки.
   *  \retval false При возникновении ошибки.
   *  \sa SendQuery(), Fetch()
   */
  bool BindCol (SQLUSMALLINT colNum, SQLSMALLINT dstType, SQLPOINTER dstValue, SQLLEN dstLength);

  /*! \brief Привязывает буфер к маркеру параметра в инструкции SQL.
   *
   *  \param colNum Номер параметра, упорядоченный последовательно
   *         в увеличивающемся порядке, начинающемся с 1.
   *  \param ioType Тип параметра.
   *         Возможные значения: SQL_PARAM_INPUT, SQL_PARAM_INPUT_OUTPUT, SQL_PARAM_OUTPUT.
   *  \param dstType Тип данных для C параметра. Наиболее часто используемые значения:
   *         SQL_C_CHAR, SQL_C_LONG, SQL_C_SHORT, SQL_C_FLOAT, SQL_C_DOUBLE,
   *         SQL_C_NUMERIC, SQL_C_DATE, SQL_C_TIME, SQL_C_TIMESTAMP,
   *         SQL_C_BINARY, SQL_C_BIT, SQL_C_SBIGINT, SQL_C_UBIGINT,
   *         SQL_C_TINYINT, SQL_C_ULONG, SQL_C_USHORT, SQL_C_UTINYINT
   *  \param srcType SQL-тип параметра.
   *  \param colSize Размер столбца или выражения, соответствующего маркеру параметра.
   *  \param numDigits Десятичные цифры столбца или выражения соответствующего маркеру параметра.
   *  \param dstValue Указатель на буфер для данных параметра.
   *  \param dstLength Длина буфера \c dstValue в байтах.
   *  \retval true Запрос выполнен успешно.
   *  \retval false Произошла ошибка при выполнении запроса.
   *  \sa PrepareQuery(), SendQuery()
   */
  bool BindParam (SQLUSMALLINT colNum, SQLSMALLINT ioType, SQLSMALLINT dstType, SQLSMALLINT srcType, SQLUINTEGER colSize, SQLSMALLINT numDigits, SQLPOINTER dstValue, SQLLEN dstLength);

  /*! \brief Готовит команду SQL к выполнению.
   *
   *  \param query SQL запрос.
   *  \retval true Подготовка выполнена успешно.
   *  \retval false Произошла ошибка при подготовке запроса.
   *  \sa BindParam(), SendQuery()
   */
  bool PrepareQuery (const string query);

  /*! \brief Выполняет подготовленную инструкцию.
   *
   *  Использует текущие (актуальные) значения переменных маркера параметра,
   *  если маркеры параметров существуют в инструкции.
   *
   *  Этот пример объясняет, как прикладная программа может использовать
   *  подготовленное выполнение. Выборка готовит инструкцию INSERT
   *  и вставляет 100 строк данных, заменяя буферные значения.
   *  \code
   *  SQLINTEGER id;
   *  SQLCHAR name[30];
   *  DBQuery query(mydb);
   *  query.PrepareQuery("INSERT INTO EMP(ID, NAME) VALUES(?, ?)");
   *  query.BindParam(1, SQL_PARAM_INPUT, SQL_C_LONG, SQL_INTEGER, 0, 0, &id, 0);
   *  query.BindParam(2, SQL_PARAM_INPUT, SQL_C_CHAR, SQL_VARCHAR, 0, 0, name, sizeof(name));
   *  for (id=1; id<=100; id++)
   *  {
   *    sprintf(name, "id is %d", id);
   *    query.SendQuery();
   *  }
   *  \endcode
   *
   *  \retval true Запрос выполнен успешно.
   *  \retval false Произошла ошибка при выполнении запроса.
   *  \sa PrepareQuery(), BindParam()
   */
  bool SendQuery ();

  /*! \brief Выполняет прямой запрос \c query.
   *
   *  Прямое выполнение представляет собой самый простой способ выполнить инструкцию.
   *  Прямое выполнение обычно используется универсальными прикладными программами,
   *  которые формируют и выполняют инструкции во время выполнения.
   *  Например, следующий код формирует инструкции SQL и выполняет их один раз:
   *  \code
   *  DBQuery query(mydb);
   *  query.SendQueryDirect("CREATE TABLE my_test(id int, name text)");
   *  query.SendQueryDirect("INSERT INTO my_test VALUES(20, 'hello')");
   *  \endcode
   *
   *  \param query SQL запрос.
   *  \retval true Запрос выполнен успешно.
   *  \retval false Произошла ошибка при выполнении запроса.
   */
  bool SendQueryDirect (const string query);

  /*! Выбирает следующую строку данных из набора результатов
   *  и возвращает данные для всех связанных столбцов
   *  \retval SQL_NO_DATA Если в наборе результатов не осталось строк с данными.
   */
  SQLRETURN Fetch ();

  /*! Возвращает количество строк, на которые воздействует инструкция UPDATE, INSERT или DELETE.
   *
   */
  SQLINTEGER RowsCount ();

  void reset ();

protected:
    bool CreateStatement ();
  void DestroyStatement ();

  //! Database to use
  DB *db;
  //! Handle for a statement
  SQLHSTMT statement;
};


#endif /* #ifndef SAMS_DB_H */
