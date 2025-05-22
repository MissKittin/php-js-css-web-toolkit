# [1.1]

### Changed

- `uri_router.php` checks if the route callback returns `true` (cache redesigned)
- From now on, the cached routing table is encapsulated in an anonymous function
- Added `return` in the `run_callback` method (see documentation)
