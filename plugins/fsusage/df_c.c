/* df - summarize free disk space
   Copyright (C) 91, 1995-2007 Free Software Foundation, Inc.

   This program is free software; you can redistribute it and/or modify
   it under the terms of the GNU General Public License as published by
   the Free Software Foundation; either version 2, or (at your option)
   any later version.

   This program is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with this program; if not, write to the Free Software Foundation,
   Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.  */

/* Written by David MacKenzie <djm@gnu.ai.mit.edu>.
   --human-readable and --megabyte options added by lm@sgi.com.
   --si and large file support added by eggert@twinsun.com.  */

#include "config.h"
#include <stdio.h>
#include <sys/types.h>
#include <getopt.h>
#include <unistd.h>

#include "system.h"
/*
#include "canonicalize.h"
#include "error.h"
*/
#include "fsusage_c.h"
#include "human.h"
#include "inttostr.h"
#include "mountlist.h"
/*
#include "quote.h"
*/
#include "save-cwd.h"

/* If true, show inode information. */
bool inode_format;

/* If true, show even file systems with zero size or
   uninteresting types. */
bool show_all_fs;

/* If true, show only local file systems.  */
bool show_local_fs;

/* If true, output data for each file system corresponding to a
   command line argument -- even if it's a dummy (automounter) entry.  */
bool show_listed_fs;

/* Human-readable options for output.  */
int human_output_opts;

/* The units to use when printing sizes.  */
uintmax_t output_block_size;

/* If true, use the POSIX output format.  */
bool posix_format;

/* True if a file system has been processed for output.  */
bool file_systems_processed;

/* If true, invoke the `sync' system call before getting any usage data.
   Using this option can make df very slow, especially with many or very
   busy disks.  Note that this may make a difference on some systems --
   SunOS 4.1.3, for one.  It is *not* necessary on Linux.  */
bool require_sync;

/* Desired exit status.  */
int exit_status;
char exit_message[1024];

/* A file system type to display. */

struct fs_type_list
{
  char *fs_name;
  struct fs_type_list *fs_next;
};

/* Linked list of file system types to display.
   If `fs_select_list' is NULL, list all types.
   This table is generated dynamically from command-line options,
   rather than hardcoding into the program what it thinks are the
   valid file system types; let the user specify any file system type
   they want to, and if there are any file systems of that type, they
   will be shown.

   Some file system types:
   4.2 4.3 ufs nfs swap ignore io vm efs dbg */

struct fs_type_list *fs_select_list;

/* Linked list of file system types to omit.
   If the list is empty, don't exclude any types.  */

struct fs_type_list *fs_exclude_list;

/* Linked list of mounted file systems. */
struct mount_entry *mount_list;

/* If true, print file system type as well.  */
bool print_type;

/* For long options that have no equivalent short option, use a
   non-character as a pseudo short option, starting with CHAR_MAX + 1.  */
enum
{
  NO_SYNC_OPTION = CHAR_MAX + 1,
  /* FIXME: --kilobytes is deprecated (but not -k); remove in late 2006 */
  KILOBYTES_LONG_OPTION,
  SYNC_OPTION
};

char *
print_header (void)
{
  char *str_header;
  char str_res[1024];
  char str_tmp[256];

  char buf[MAX (LONGEST_HUMAN_READABLE + 1, INT_BUFSIZE_BOUND (uintmax_t))];

  if (print_type)
    strcpy (str_res, "Filesystem    Type");
  else
    strcpy (str_res, "Filesystem        ");

  if (inode_format)
    strcat (str_res, "    Inodes   IUsed   IFree IUse%%");
  else if (human_output_opts & human_autoscale)
    {
      if (human_output_opts & human_base_1024)
	strcat (str_res, "    Size  Used Avail Use%%");
      else
	strcat (str_res, "     Size   Used  Avail Use%%");
    }
  else if (posix_format)
    {
      sprintf (str_tmp, " %s-blocks      Used Available Capacity",
	    umaxtostr (output_block_size, buf));
      strcat (str_res, str_tmp);
    }
  else
    {
      int opts = (human_suppress_point_zero
		  | human_autoscale | human_SI
		  | (human_output_opts
		     & (human_group_digits | human_base_1024 | human_B)));

      /* Prefer the base that makes the human-readable value more exact,
	 if there is a difference.  */

      uintmax_t q1000 = output_block_size;
      uintmax_t q1024 = output_block_size;
      bool divisible_by_1000;
      bool divisible_by_1024;

      do
	{
	  divisible_by_1000 = q1000 % 1000 == 0;  q1000 /= 1000;
	  divisible_by_1024 = q1024 % 1024 == 0;  q1024 /= 1024;
	}
      while (divisible_by_1000 & divisible_by_1024);

      if (divisible_by_1000 < divisible_by_1024)
	opts |= human_base_1024;
      if (divisible_by_1024 < divisible_by_1000)
	opts &= ~human_base_1024;
      if (! (opts & human_base_1024))
	opts |= human_B;

      sprintf (str_tmp, " %4s-blocks      Used Available Use%%",
	      human_readable (output_block_size, buf, opts, 1, 1));
      strcat (str_res, str_tmp);
    }

  strcat (str_res, " Mounted on\n");
  str_header = (char*) malloc(strlen(str_res));
  strcpy (str_header, str_res);
  return str_header;
}

/* Is FSTYPE a type of file system that should be listed?  */

bool
selected_fstype (const char *fstype)
{
  const struct fs_type_list *fsp;

  if (fs_select_list == NULL || fstype == NULL)
    return true;
  for (fsp = fs_select_list; fsp; fsp = fsp->fs_next)
    if (STREQ (fstype, fsp->fs_name))
      return true;
  return false;
}

/* Is FSTYPE a type of file system that should be omitted?  */

bool
excluded_fstype (const char *fstype)
{
  const struct fs_type_list *fsp;

  if (fs_exclude_list == NULL || fstype == NULL)
    return false;
  for (fsp = fs_exclude_list; fsp; fsp = fsp->fs_next)
    if (STREQ (fstype, fsp->fs_name))
      return true;
  return false;
}

/* Like human_readable (N, BUF, human_output_opts, INPUT_UNITS, OUTPUT_UNITS),
   except:

    - If NEGATIVE, then N represents a negative number,
      expressed in two's complement.
    - Otherwise, return "-" if N is UINTMAX_MAX.  */

char const *
df_readable (bool negative, uintmax_t n, char *buf,
	     uintmax_t input_units, uintmax_t output_units)
{
  if (n == UINTMAX_MAX && !negative)
    return "-";
  else
    {
      char *p = human_readable (negative ? -n : n, buf + negative,
				human_output_opts, input_units, output_units);
      if (negative)
	*--p = '-';
      return p;
    }
}

/* Display a space listing for the disk device with absolute file name DISK.
   If MOUNT_POINT is non-NULL, it is the name of the root of the
   file system on DISK.
   If STAT_FILE is non-null, it is the name of a file within the file
   system that the user originally asked for; this provides better
   diagnostics, and sometimes it provides better results on networked
   file systems that give different free-space results depending on
   where in the file system you probe.
   If FSTYPE is non-NULL, it is the type of the file system on DISK.
   If MOUNT_POINT is non-NULL, then DISK may be NULL -- certain systems may
   not be able to produce statistics in this case.
   ME_DUMMY and ME_REMOTE are the mount entry flags.  */

char *
show_dev (char const *disk, char const *mount_point,
	  char const *stat_file, char const *fstype,
	  bool me_dummy, bool me_remote)
{
  struct fs_usage fsu;
  char buf[3][LONGEST_HUMAN_READABLE + 2];
  int width;
  int col1_adjustment = 0;
  int use_width;
  uintmax_t input_units;
  uintmax_t output_units;
  uintmax_t total;
  uintmax_t available;
  bool negate_available;
  uintmax_t available_to_root;
  uintmax_t used;
  bool negate_used;
  double pct = -1;

  char str_res[1024];
  char str_tmp[256];
  char *str_dev = NULL;

  if (me_remote & show_local_fs)
    return NULL;

  if (me_dummy & !show_all_fs & !show_listed_fs)
    return NULL;

  if (!selected_fstype (fstype) || excluded_fstype (fstype))
    return NULL;

  /* If MOUNT_POINT is NULL, then the file system is not mounted, and this
     program reports on the file system that the special file is on.
     It would be better to report on the unmounted file system,
     but statfs doesn't do that on most systems.  */
  if (!stat_file)
    stat_file = mount_point ? mount_point : disk;

  if (get_fs_usage (stat_file, disk, &fsu))
    {
//      error (0, errno, "%s", quote (stat_file));
      return NULL;
    }

  if (fsu.fsu_blocks == 0 && !show_all_fs && !show_listed_fs)
    return NULL;

  if (! file_systems_processed)
    {
      file_systems_processed = true;
      str_dev = print_header ();
    }

  if (! disk)
    disk = "-";			/* unknown */
  if (! fstype)
    fstype = "-";		/* unknown */

  /* df.c reserved 5 positions for fstype,
     but that does not suffice for type iso9660 */
  if (print_type)
    {
      size_t disk_name_len = strlen (disk);
      size_t fstype_len = strlen (fstype);
      if (disk_name_len + fstype_len < 18)
	sprintf (str_tmp, "%s%*s  ", disk, 18 - (int) disk_name_len, fstype);
      else if (!posix_format)
	sprintf (str_tmp, "%s\n%18s  ", disk, fstype);
      else
	sprintf (str_tmp, "%s %s", disk, fstype);
    }
  else
    {
      if (strlen (disk) > 20 && !posix_format)
	sprintf (str_tmp, "%s\n%20s", disk, "");
      else
	sprintf (str_tmp, "%-20s", disk);
    }
  strcpy (str_res, str_tmp);

  if (inode_format)
    {
      width = 7;
      use_width = 5;
      input_units = output_units = 1;
      total = fsu.fsu_files;
      available = fsu.fsu_ffree;
      negate_available = false;
      available_to_root = available;
    }
  else
    {
      if (human_output_opts & human_autoscale)
	width = 5 + ! (human_output_opts & human_base_1024);
      else
	{
	  width = 9;
	  if (posix_format)
	    {
	      uintmax_t b;
	      col1_adjustment = -3;
	      for (b = output_block_size; 9 < b; b /= 10)
		col1_adjustment++;
	    }
	}
      use_width = ((posix_format
		    && ! (human_output_opts & human_autoscale))
		   ? 8 : 4);
      input_units = fsu.fsu_blocksize;
      output_units = output_block_size;
      total = fsu.fsu_blocks;
      available = fsu.fsu_bavail;
      negate_available = (fsu.fsu_bavail_top_bit_set
			  & (available != UINTMAX_MAX));
      available_to_root = fsu.fsu_bfree;
    }

  used = UINTMAX_MAX;
  negate_used = false;
  if (total != UINTMAX_MAX && available_to_root != UINTMAX_MAX)
    {
      used = total - available_to_root;
      negate_used = (total < available_to_root);
    }

  sprintf (str_tmp, " %*s %*s %*s ",
	  width + col1_adjustment,
	  df_readable (false, total,
		       buf[0], input_units, output_units),
	  width, df_readable (negate_used, used,
			      buf[1], input_units, output_units),
	  width, df_readable (negate_available, available,
			      buf[2], input_units, output_units));
  strcat (str_res, str_tmp);

  if (used == UINTMAX_MAX || available == UINTMAX_MAX)
    ;
  else if (!negate_used
	   && used <= TYPE_MAXIMUM (uintmax_t) / 100
	   && used + available != 0
	   && (used + available < used) == negate_available)
    {
      uintmax_t u100 = used * 100;
      uintmax_t nonroot_total = used + available;
      pct = u100 / nonroot_total + (u100 % nonroot_total != 0);
    }
  else
    {
      /* The calculation cannot be done easily with integer
	 arithmetic.  Fall back on floating point.  This can suffer
	 from minor rounding errors, but doing it exactly requires
	 multiple precision arithmetic, and it's not worth the
	 aggravation.  */
      double u = negate_used ? - (double) - used : used;
      double a = negate_available ? - (double) - available : available;
      double nonroot_total = u + a;
      if (nonroot_total)
	{
	  long int lipct = pct = u * 100 / nonroot_total;
	  double ipct = lipct;

	  /* Like `pct = ceil (dpct);', but avoid ceil so that
	     the math library needn't be linked.  */
	  if (ipct - 1 < pct && pct <= ipct + 1)
	    pct = ipct + (ipct < pct);
	}
    }

  if (0 <= pct)
    sprintf (str_tmp, "%*.0f%%", use_width - 1, pct);
  else
    sprintf (str_tmp, "%*s", use_width, "- ");
  strcat (str_res, str_tmp);

  if (mount_point)
    {
#ifdef HIDE_AUTOMOUNT_PREFIX
      /* Don't print the first directory name in MOUNT_POINT if it's an
	 artifact of an automounter.  This is a bit too aggressive to be
	 the default.  */
      if (strncmp ("/auto/", mount_point, 6) == 0)
	mount_point += 5;
      else if (strncmp ("/tmp_mnt/", mount_point, 9) == 0)
	mount_point += 8;
#endif
      sprintf (str_tmp, " %s", mount_point);
      strcat (str_res, str_tmp);
    }
  strcat (str_res, "\n");

  if (str_dev)
    {
      str_dev = (char*)realloc(str_dev, strlen (str_dev) + strlen (str_res));
    }
  else
    {
      str_dev = (char*)malloc(strlen(str_res));
      str_dev[0] = '\0';
    }
  strcat (str_dev, str_res);
  return str_dev;
}

/* Return the root mountpoint of the file system on which FILE exists, in
   malloced storage.  FILE_STAT should be the result of stating FILE.
   Give a diagnostic and return NULL if unable to determine the mount point.
   Exit if unable to restore current working directory.  */
char *
find_mount_point (const char *file, const struct stat *file_stat)
{
  struct saved_cwd cwd;
  struct stat last_stat;
  char *mp = NULL;		/* The malloced mount point.  */

  if (save_cwd (&cwd) != 0)
    {
      //error (0, errno, "cannot get current directory");
      return NULL;
    }

  if (S_ISDIR (file_stat->st_mode))
    /* FILE is a directory, so just chdir there directly.  */
    {
      last_stat = *file_stat;
      if (chdir (file) < 0)
	{
	  //error (0, errno, "cannot change to directory %s", quote (file));
	  return NULL;
	}
    }
  else
    /* FILE is some other kind of file; use its directory.  */
    {
      char *xdir = dir_name (file);
      char *dir;
      ASSIGN_STRDUPA (dir, xdir);
      free (xdir);

      if (chdir (dir) < 0)
	{
	  //error (0, errno, "cannot change to directory %s", quote (dir));
	  return NULL;
	}

      if (stat (".", &last_stat) < 0)
	{
	  //error (0, errno, "cannot stat current directory (now %s)", quote (dir));
	  goto done;
	}
    }

  /* Now walk up FILE's parents until we find another file system or /,
     chdiring as we go.  LAST_STAT holds stat information for the last place
     we visited.  */
  for (;;)
    {
      struct stat st;
      if (stat ("..", &st) < 0)
	{
	  //error (0, errno, "cannot stat %s", quote (".."));
	  goto done;
	}
      if (st.st_dev != last_stat.st_dev || st.st_ino == last_stat.st_ino)
	/* cwd is the mount point.  */
	break;
      if (chdir ("..") < 0)
	{
	  //error (0, errno, "cannot change to directory %s", quote (".."));
	  goto done;
	}
      last_stat = st;
    }

  /* Finally reached a mount point, see what it's called.  */
  mp = getcwd (NULL, 0);

done:
  /* Restore the original cwd.  */
  {
    int save_errno = errno;
    if (restore_cwd (&cwd) != 0)
      {
        //error (EXIT_FAILURE, errno, "failed to return to initial working directory");
      }
    free_cwd (&cwd);
    errno = save_errno;
  }

  return mp;
}

/* If DISK corresponds to a mount point, show its usage
   and return true.  Otherwise, return false.  */
bool
show_disk (char const *disk)
{
  struct mount_entry const *me;
  struct mount_entry const *best_match = NULL;

  for (me = mount_list; me; me = me->me_next)
    if (STREQ (disk, me->me_devname))
      best_match = me;

  if (best_match)
    {
      show_dev (best_match->me_devname, best_match->me_mountdir, NULL,
		best_match->me_type, best_match->me_dummy,
		best_match->me_remote);
      return true;
    }

  return false;
}

/* Figure out which device file or directory POINT is mounted on
   and show its disk usage.
   STATP must be the result of `stat (POINT, STATP)'.  */
void
show_point (const char *point, const struct stat *statp)
{
  struct stat disk_stats;
  struct mount_entry *me;
  struct mount_entry const *best_match = NULL;

  /* If POINT is an absolute file name, see if we can find the
     mount point without performing any extra stat calls at all.  */
  if (*point == '/')
    {
      /* Find the best match: prefer non-dummies, and then prefer the
	 last match if there are ties.  */

      for (me = mount_list; me; me = me->me_next)
	if (STREQ (me->me_mountdir, point) && !STREQ (me->me_type, "lofs")
	    && (!best_match || best_match->me_dummy || !me->me_dummy))
	  best_match = me;
    }

  /* Calculate the real absolute file name for POINT, and use that to find
     the mount point.  This avoids statting unavailable mount points,
     which can hang df.  */
  if (! best_match)
    {
      char *resolved = canonicalize_file_name (point);

      if (resolved && resolved[0] == '/')
	{
	  size_t resolved_len = strlen (resolved);
	  size_t best_match_len = 0;

	  for (me = mount_list; me; me = me->me_next)
	    if (!STREQ (me->me_type, "lofs")
		&& (!best_match || best_match->me_dummy || !me->me_dummy))
	      {
		size_t len = strlen (me->me_mountdir);
		if (best_match_len <= len && len <= resolved_len
		    && (len == 1 /* root file system */
			|| ((len == resolved_len || resolved[len] == '/')
			    && strncmp (me->me_mountdir, resolved, len) == 0)))
		  {
		    best_match = me;
		    best_match_len = len;
		  }
	      }
	}

      free (resolved);

      if (best_match
	  && (stat (best_match->me_mountdir, &disk_stats) != 0
	      || disk_stats.st_dev != statp->st_dev))
	best_match = NULL;
    }

  if (! best_match)
    for (me = mount_list; me; me = me->me_next)
      {
	if (me->me_dev == (dev_t) -1)
	  {
	    if (stat (me->me_mountdir, &disk_stats) == 0)
	      me->me_dev = disk_stats.st_dev;
	    else
	      {
		/* Report only I/O errors.  Other errors might be
		   caused by shadowed mount points, which means POINT
		   can't possibly be on this file system.  */
		if (errno == EIO)
		  {
		    //error (0, errno, "%s", quote (me->me_mountdir));
		    exit_status = EXIT_FAILURE;
		  }

		/* So we won't try and fail repeatedly. */
		me->me_dev = (dev_t) -2;
	      }
	  }

	if (statp->st_dev == me->me_dev
	    && !STREQ (me->me_type, "lofs")
	    && (!best_match || best_match->me_dummy || !me->me_dummy))
	  {
	    /* Skip bogus mtab entries.  */
	    if (stat (me->me_mountdir, &disk_stats) != 0
		|| disk_stats.st_dev != me->me_dev)
	      me->me_dev = (dev_t) -2;
	    else
	      best_match = me;
	  }
      }

  if (best_match)
    show_dev (best_match->me_devname, best_match->me_mountdir, point,
	      best_match->me_type, best_match->me_dummy, best_match->me_remote);
  else
    {
      /* We couldn't find the mount entry corresponding to POINT.  Go ahead and
	 print as much info as we can; methods that require the device to be
	 present will fail at a later point.  */

      /* Find the actual mount point.  */
      char *mp = find_mount_point (point, statp);
      if (mp)
	{
	  show_dev (NULL, mp, NULL, NULL, false, false);
	  free (mp);
	}
    }
}

/* Determine what kind of node NAME is and show the disk usage
   for it.  STATP is the results of `stat' on NAME.  */

void
show_entry (char const *name, struct stat const *statp)
{
  if ((S_ISBLK (statp->st_mode) || S_ISCHR (statp->st_mode))
      && show_disk (name))
    return;

  show_point (name, statp);
}

/* Show all mounted file systems, except perhaps those that are of
   an unselected type or are empty. */

char *
show_all_entries (void)
{
  struct mount_entry *me;
  char *str_entries = NULL;
  char *str_dev = NULL;

  for (me = mount_list; me; me = me->me_next)
    {
      str_dev = show_dev (me->me_devname, me->me_mountdir, NULL, me->me_type, me->me_dummy, me->me_remote);
      if (str_dev)
        {
          if (str_entries)
            str_entries = (char*)realloc (str_entries, strlen (str_entries) + strlen (str_dev));
          else
            str_entries = str_dev;
        }
    }
  return str_entries;
}

/* Add FSTYPE to the list of file system types to display. */

void
add_fs_type (const char *fstype)
{
  struct fs_type_list *fsp;

  fsp = (struct fs_type_list *)malloc (sizeof (struct fs_type_list));
  fsp->fs_name = (char *) fstype;
  fsp->fs_next = fs_select_list;
  fs_select_list = fsp;
}

/* Add FSTYPE to the list of file system types to be omitted. */

void
add_excluded_fs_type (const char *fstype)
{
  struct fs_type_list *fsp;

  fsp = (struct fs_type_list *)malloc (sizeof (struct fs_type_list));
  fsp->fs_name = (char *) fstype;
  fsp->fs_next = fs_exclude_list;
  fs_exclude_list = fsp;
}


char *
get_fsusage ()
{
  char *str_usage = NULL;

  fs_select_list = NULL;
  fs_exclude_list = NULL;
  inode_format = false;
  show_all_fs = false;
  show_listed_fs = false;
  human_output_opts = -1;
  print_type = false;
  file_systems_processed = false;
  posix_format = false;

  // Assume -hl command line arguments
  human_output_opts = human_autoscale | human_SI | human_base_1024;
  output_block_size = 1;
  show_local_fs = true;

  if (human_output_opts == -1)
    {
      if (posix_format)
	{
	  human_output_opts = 0;
	  output_block_size = (getenv ("POSIXLY_CORRECT") ? 512 : 1024);
	}
      else
	human_output_opts = human_options (getenv ("DF_BLOCK_SIZE"), false,
					   &output_block_size);
    }

/*
  // Fail if the same file system type was both selected and excluded.
  {
    bool match = false;
    struct fs_type_list *fs_incl;
    for (fs_incl = fs_select_list; fs_incl; fs_incl = fs_incl->fs_next)
      {
	struct fs_type_list *fs_excl;
	for (fs_excl = fs_exclude_list; fs_excl; fs_excl = fs_excl->fs_next)
	  {
	    if (STREQ (fs_incl->fs_name, fs_excl->fs_name))
	      {
                sprintf(exit_message, "file system type %s both selected and excluded",
                       quote (fs_incl->fs_name));
                return exit_message;
		match = true;
		break;
	      }
	  }
      }
    if (match)
      exit (EXIT_FAILURE);
  }
*/

  mount_list =
    read_file_system_list ((fs_select_list != NULL
			    || fs_exclude_list != NULL
			    || print_type
			    || show_local_fs));

  if (mount_list == NULL)
    {
      /* Couldn't read the table of mounted file systems.
         Fail, because df was invoked with no file name arguments;
      */
      str_usage = (char*)malloc (strlen("Cannot read table of mounted file systems")+1);
      strcpy (str_usage, "Cannot read table of mounted file systems");
    }

  if (require_sync)
    sync ();

  str_usage = show_all_entries ();

  if (! file_systems_processed && ! str_usage)
    {
      str_usage = (char*)malloc (strlen("no file systems processed")+1);
      strcpy (str_usage, "no file systems processed");
    }

  return str_usage;
}
