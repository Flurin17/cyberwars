# Luzerner Moments – Simple PHP Gallery

A minimal PHP site for uploading and showcasing photos for the #LuzernerMoments CTF theme. Includes:

- Landing page with upload form (`index.php`)
- Upload handler with safe image re-encoding (`upload.php`)
- Gallery grid (`gallery.php`)
- Thank-you page (`thanks.php`)

## Running locally

- PHP 8.0+ with GD extension enabled.
- Start a local server in the project root:

```bash
php -S localhost:8000
```

Open `http://localhost:8000`.

## Storage

- Uploaded images are stored in `uploads/` as JPEG.
- Submissions are appended to `data/submissions.jsonl` (JSON Lines).

## Security notes

- Images are validated and re-encoded to JPEG to drop metadata and executable payloads.
- CSRF protection on form submission.
- Text inputs are sanitized and escaped.

This project is intended for educational purposes.


