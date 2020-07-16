# 2dos

## Database Management

- [ ] Create mimetype specific DB inputs where needed (e.g. for videofiles/metadata)
- [ ] Extract video/audio metadata (length etc.)

## File Processing

- [x] Deletion of deleted images from DB and thumbnails/intermediates
- [x] Ensure that only compatible files are being used
- [ ] Optimize large archive processing
- [x] Fix that image thumbnails/intermediates are saved with the original file extension (e.g. bmp) instead of .jpg
- [ ] Optimize gif thumbs (the generated thumbs are way too big. Either optimize or use still images or mp4s instead)
- [x] Add process time measurement for future optimizations

## Frontend

* Map
  * [x] Finalize long/lat usage in file details (works, I think?)
  * [ ] Frontend GMaps API Key input formular