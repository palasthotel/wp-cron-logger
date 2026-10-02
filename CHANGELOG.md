# Changelog

## [1.3.5](https://github.com/palasthotel/wp-cron-logger/compare/v1.3.4...v1.3.5) (2026-10-02)


### Bug Fixes

* keep the markup the plugin logs itself ([6e049e3](https://github.com/palasthotel/wp-cron-logger/commit/6e049e30dc05af9765a7e4e7b70cf0860a751365))
* register the cron hook callbacks with zero accepted args (reported by Andis Grossteins) ([80cdcc1](https://github.com/palasthotel/wp-cron-logger/commit/80cdcc13c0106d7e226f184cea30e7fc11731933)), closes [#9](https://github.com/palasthotel/wp-cron-logger/issues/9)
* remove expired runs from the log table again ([9f85247](https://github.com/palasthotel/wp-cron-logger/commit/9f852475e6b3de5d857824c09761f140680aaf0b))

## [1.3.4](https://github.com/palasthotel/wp-cron-logger/compare/v1.3.3...v1.3.4) (2026-07-31)


### Bug Fixes

* bind the values interpolated into the log queries ([85ad8b4](https://github.com/palasthotel/wp-cron-logger/commit/85ad8b41b9b2d3ddb1a245d91e52d38f7daa2915))
* deliver the orphaned child log cleanup and its database error fix ([758a81f](https://github.com/palasthotel/wp-cron-logger/commit/758a81f19761e215fedcc49fa3dad2a8a1730968))
* escape the log page output ([fc68ebb](https://github.com/palasthotel/wp-cron-logger/commit/fc68ebb5f8393868514e004922bd50ff968ba130))
* restrict the log cleanup endpoint to administrators (CVE-2025-53266, reported by Nguyen Xuan Chien, fix by Quentin Lienhardt) ([1ec1272](https://github.com/palasthotel/wp-cron-logger/commit/1ec12725ebce5b8823005941678027ff007fd8ca))

## Changelog
