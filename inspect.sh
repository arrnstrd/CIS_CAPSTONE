echo "=== SETTINGS DIR LISTING ==="
find resources/views/pov/teacher/settings -type f
echo "=== SETTINGS FILES CONTENT ==="
find resources/views/pov/teacher/settings -type f | while read f; do echo "--- $f ---"; cat "$f"; done
echo "=== ACCOUNT LAYOUT FULL ==="
find resources/views -iregex ".*account-management.*" -exec cat {} \;
echo "=== TOP NAV SEARCH ==="
grep -rl "displayName" resources/views/components/layouts/teacher/
echo "=== ACCOUNT MGMT CSS FILES ==="
grep -rl "account-mgmt" resources/css/
