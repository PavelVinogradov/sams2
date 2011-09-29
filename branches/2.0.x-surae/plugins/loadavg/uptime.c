#include "config.h"

#include <stdio.h>
#include <string.h>
#include <stdlib.h>
#include <time.h>
#include <errno.h>
#include <limits.h>

#include "readutmp.h"

/* True if the arithmetic type T is signed.  */
#define TYPE_SIGNED(t) (! ((t) 0 < (t) -1))

/* The maximum and minimum values for the integer type T.  These
 *    macros have undefined behavior if T is signed and has padding bits.
 *       If this is a problem for you, please let us know how to fix it for
 *          your host.  */
#define TYPE_MINIMUM(t) \
  ((t) (! TYPE_SIGNED (t) \
        ? (t) 0 \
        : TYPE_SIGNED_MAGNITUDE (t) \
        ? ~ (t) 0 \
        : ~ (t) 0 << (sizeof (t) * CHAR_BIT - 1)))
#define TYPE_MAXIMUM(t) \
  ((t) (! TYPE_SIGNED (t) \
        ? (t) -1 \
        : ~ (~ (t) 0 << (sizeof (t) * CHAR_BIT - 1))))

const char *
print_uptime (size_t n, const STRUCT_UTMP *this)
{
  size_t entries = 0;
  time_t boot_time = 0;
  time_t time_now;
  time_t uptime = 0;
  long int updays;
  int uphours;
  int upmins;
  /*struct tm *tmn;*/
  double avg[3];
  int loads;
  char *str_uptime = (char*)malloc(1024);
  char str_uptime_tmp[100];
  memset (str_uptime, 0, 1024);
  memset (str_uptime_tmp, 0, sizeof (str_uptime_tmp));
#ifdef HAVE_PROC_UPTIME
  FILE *fp;

  fp = fopen ("/proc/uptime", "r");
  if (fp != NULL)
    {
      char buf[BUFSIZ];
      char *b = fgets (buf, BUFSIZ, fp);
      if (b == buf)
        {
          char *end_ptr;
          double upsecs = strtod (buf, &end_ptr);
          if (buf != end_ptr)
            uptime = (0 <= upsecs && upsecs < TYPE_MAXIMUM (time_t)
                      ? upsecs : -1);
        }

      fclose (fp);
    }
#endif /* HAVE_PROC_UPTIME */

#if HAVE_SYSCTL && defined CTL_KERN && defined KERN_BOOTTIME
  {
    /* FreeBSD specific: fetch sysctl "kern.boottime".  */
    static int request[2] = { CTL_KERN, KERN_BOOTTIME };
    struct timeval result;
    size_t result_len = sizeof result;

    if (sysctl (request, 2, &result, &result_len, NULL, 0) >= 0)
      boot_time = result.tv_sec;
  }
#endif

#if HAVE_OS_H /* BeOS */
  {
    system_info si;

    get_system_info (&si);
    boot_time = si.boot_time / 1000000;
  }
#endif

#if HAVE_UTMPX_H || HAVE_UTMP_H
  /* Loop through all the utmp entries we just read and count up the valid
 *      ones, also in the process possibly gleaning boottime. */
  while (n--)
    {
      entries += IS_USER_PROCESS (this);
      if (UT_TYPE_BOOT_TIME (this))
        boot_time = UT_TIME_MEMBER (this);
      ++this;
    }
#endif
  time_now = time (NULL);
#if defined HAVE_PROC_UPTIME
  if (uptime == 0)
#endif
    {
      if (boot_time == 0)
        return "couldn't get boot time";
      uptime = time_now - boot_time;
    }
  updays = uptime / 86400;
  uphours = (uptime - (updays * 86400)) / 3600;
  upmins = (uptime - (updays * 86400) - (uphours * 3600)) / 60;

/*
  // Print current time
  tmn = localtime (&time_now);
  if (tmn)
    strftime(str_uptime_tmp, sizeof(str_uptime_tmp), "%H:%M:%S", tmn);
  else
    sprintf (str_uptime_tmp, "??:??:??");
  strcat (str_uptime, str_uptime_tmp);
*/

  strcat (str_uptime, "<table width=100% class=sysplugtable>\n");
  strcat (str_uptime, "<th width=30%>Uptime</th>\n");
  strcat (str_uptime, "<th width=30%>Logged in Users</th>\n");
  strcat (str_uptime, "<th width=40%>Load average</th>\n");
  strcat (str_uptime, "<tr>\n");

  // Print uptime
  strcat (str_uptime, "  <td align=\"center\">");
  if (uptime == (time_t) -1)
    strcat (str_uptime, "???? days ??:??");
  else
    {
      if (0 < updays)
        {
          sprintf (str_uptime_tmp, "%ld day[s]", updays);
          strcat (str_uptime, str_uptime_tmp);
        }
      sprintf (str_uptime_tmp, "%2d:%02d", uphours, upmins);
      strcat (str_uptime, str_uptime_tmp);
    }
  strcat (str_uptime, "</td>\n");

  // Print count of logged in users
  strcat (str_uptime, "  <td align=\"center\">");
  sprintf (str_uptime_tmp, "%lu", (unsigned long int) entries);
  strcat (str_uptime, str_uptime_tmp);
  strcat (str_uptime, "</td>\n");

#if defined HAVE_GETLOADAVG || defined C_GETLOADAVG
  loads = getloadavg (avg, 3);
#else
  loads = -1;
#endif

  // Print load average
  strcat (str_uptime, "  <td align=\"center\">");
  if (loads > 0)
    {
      sprintf (str_uptime_tmp, "%.2f", avg[0]);
      strcat (str_uptime, str_uptime_tmp);
    }
  if (loads > 1)
    {
      sprintf (str_uptime_tmp, ", %.2f", avg[1]);
      strcat (str_uptime, str_uptime_tmp);
    }
  if (loads > 2)
    {
      sprintf (str_uptime_tmp, ", %.2f", avg[2]);
      strcat (str_uptime, str_uptime_tmp);
    }
  strcat (str_uptime, "</td>\n");
  strcat (str_uptime, "</tr>\n");
  strcat (str_uptime, "</table>\n");

  return str_uptime;
}

/* Display the system uptime and the number of users on the system,
 *    according to utmp file FILENAME.  Use read_utmp OPTIONS to read the
 *       utmp file.  */

const char *
uptime (const char *filename, int options)
{
  size_t n_users;
  STRUCT_UTMP *utmp_buf;

#if HAVE_UTMPX_H || HAVE_UTMP_H
  if (read_utmp (filename, &n_users, &utmp_buf, options) != 0)
  // "%s: %s", filename, strerror(errno)
    return strerror(errno);
#endif

  return print_uptime (n_users, utmp_buf);
}

const char *
get_uptime ()
{
  return uptime (UTMP_FILE, READ_UTMP_CHECK_PIDS);
}
