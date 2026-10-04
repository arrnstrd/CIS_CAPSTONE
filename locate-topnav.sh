echo "=== teacher.blade.php layout (main wrapper used by x-layouts.teacher) ==="
find resources/views/components/layouts -iname "teacher.blade.php" -exec cat {} \;
echo "=== search whole views tree for profile/avatar/top-nav patterns ==="
grep -rl "profile-dropdown\|topnav\|top-nav\|user-avatar\|navbar-profile" resources/views/ 2>/dev/null
echo "=== search for where teacher name/email currently renders in a non-account layout ==="
grep -rln "first_name . '\'' '\'' . \$user->last_name\|teacher?->full_name" resources/views/components/ 2>/dev/null
