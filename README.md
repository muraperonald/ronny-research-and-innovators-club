# R-RIC: Ronny Research & Innovation Club

a student club website built on the


## 1. File structure

```
ronny-research-club/
├── index.html            Home: hero, next-event card, stats, highlights, upcoming events
├── about.html            About Us: background, vision, mission, objectives, committee, contact
├── events.html           Activities/Events: filterable calendar (JS array), weekly table
├── gallery.html          Gallery: responsive CSS grid, category filter, lightbox
├── contact.html          Join / Contact: registration form, tiers table, message form, map
├── support.html          Donate & Support Us: funding goals, pledge form, other ways to help
├── process-join.php      Handles all three forms (join / contact / donation)
├── members.php           "Member wall": reads registrations back from the database
├── db.sql                MySQL schema (members, messages, donations)
├── includes/
│   ├── config.php        DB credentials (env vars or defaults) + validation patterns
│   ├── functions.php     clean(), PDO connection, validation, save + JSON fallback
│   ├── layout.php        Shared header/nav/footer for the PHP pages
│   └── .htaccess         Blocks direct web access
├── data/                 JSON fallback storage (used when MySQL is unavailable)
│   └── .htaccess         Blocks direct web access
├── css/
│   ├── bootstrap.min.css, bootstrap-icons.css
│   ├── templatemo-tiya-golf-club.css   (original template, unchanged)
│   └── custom-styles.css               (all club styling and overrides)
├── js/
│   ├── main.js           Arrays/objects, DOM rendering, widgets, form validation
│   └── jquery.min.js, bootstrap.bundle.min.js, jquery.sticky.js, animated-headline.js
├── images/               logo.svg, logo-full.svg, favicon.png, gallery/, committee/
├── fonts/                Bootstrap Icons font
├── .htaccess             No directory listing, hides db.sql
└── Dockerfile            For Render / Docker hosting
```

All paths are relative, so the folder works at a domain root or in a sub-folder.

## 2. Run it locally (XAMPP / WAMP / MAMP)

1. Copy the `ronny-research-club` folder into `htdocs/` (XAMPP) or `www/` (WAMP).
2. Start **Apache** and **MySQL**.
3. Open phpMyAdmin → **Import** → choose `db.sql` → **Go**.
   (Optional: the PHP script also creates the tables automatically.)
4. Check `includes/config.php`. XAMPP defaults (`root`, empty password) already match.
5. Visit `http://localhost/ronny-research-club/`.

No MySQL? The forms still work: submissions are saved to `data/*.json`, and the
confirmation page shows "Saved to: JSON file".

## 3. Deploy

### InfinityFree (free PHP + MySQL)
1. Create a MySQL database in the control panel and note the host, database name, user and password.
2. Put those values in `includes/config.php` (`DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`).
3. In phpMyAdmin, import `db.sql`, **deleting the first two statements**
   (`CREATE DATABASE` and `USE`) because the host already created the database.
4. Upload everything **inside** `ronny-research-club/` to `htdocs/` with the File Manager or FTP.
5. Visit your `*.infinityfreeapp.com` URL and submit a test registration.

### Render (Docker) + TiDB Cloud (free MySQL)
1. **Database:** create a free *Serverless* cluster at tidbcloud.com → **Connect** →
   copy host, port (4000), user (`xxxx.root`) and generate a password. Database: `test`.
2. **Code:** push this folder to a GitHub repository (private is fine).
3. **Render:** New → **Blueprint** → choose the repo. `render.yaml` creates a free
   Docker web service; paste `DB_HOST`, `DB_USER`, `DB_PASS` when asked.
   (`DB_PORT=4000`, `DB_NAME=test`, `DB_SSL=true` are preset.)
4. Open the `*.onrender.com` URL, submit a test registration and check that
   "Saved to" says **MySQL**.

The free service sleeps after 15 minutes idle (first visit then takes ~30 s).
If the database is unreachable, the site falls back to JSON files, which Render
wipes on every deploy.

## 4. Things to customise

| What | Where |
|---|---|
| Committee names, photos, bios | `committeeMembers` in `js/main.js` + the table in `about.html` |
| Events | `clubEvents` in `js/main.js` |
| Past events & highlights | `pastHighlights` in `js/main.js` (photos in `images/gallery/`) |
| Publications | `publications` in `js/main.js` (empty list shows a "coming soon" note) |
| Student-ID format | `STUDENT_ID_PATTERN` in **both** `js/main.js` and `includes/config.php` |
| Allowed e-mail domains | `UNI_EMAIL_PATTERN` in both files (currently any `.ac.xx` or `.edu` address) |
| Contact e-mail / phone | `about.html`, `contact.html`, `support.html`, footers, `includes/layout.php` |
| Campus map | Replace the inline SVG in `contact.html` with a Google Maps `<iframe>` |

The shared navbar and footer are copied into every page. If you change a link,
change it in all six HTML files and in `includes/layout.php`.

## 5. Rubric checklist

| Requirement | Where it is demonstrated |
|---|---|
| 5+ interconnected pages | 6 pages sharing one navbar/footer, `.active` + `aria-current` on the current page |
| Semantic HTML | `<header> <nav> <main> <section> <article> <aside> <figure> <footer> <address>` |
| Headings, lists, links, images | Every page; `ol.objective-list`, `ul.category-list`, lazy-loaded images |
| Tables | Committee directory (about), weekly schedule (events), membership tiers (contact), gift impact (support), member wall (members.php) |
| Forms | Join, Contact and Donation forms |
| External CSS + box model | `css/custom-styles.css` §4 `.highlight-card`: explicit margin/border/padding/`box-sizing` |
| Positioning | `position: fixed` (back-to-top, lightbox), `absolute` (event badges, next-event label, map pin), `relative` + `z-index` (stats strip), sticky navbar |
| Backgrounds | Per-page hero images, gradient overlays, `section-bg-image` |
| Transitions / animation | Card hover lifts, nav underline, gallery zoom, shake on error, fade-in |
| Navigation styling | Animated underline, active state, responsive collapse menu |
| JS variables, operators, conditionals, loops, functions | Throughout `js/main.js` (`for` loop in `renderEvents`, `forEach`, `for...in`) |
| Array of objects | `clubEvents`, `pastHighlights`, `publications`, `committeeMembers`, `categoryInfo` in `js/main.js` |
| DOM rendering with template literals | `eventCardHTML()`, `eventPreviewHTML()`, `renderCommittee()` |
| Events | `DOMContentLoaded`, `onclick` (theme toggle, lightbox), `onmouseover`/`onmouseout` (committee bios), `input`, `change`, `submit`, `keydown`, `scroll` |
| Interactive widgets | Event filter + search, gallery lightbox + filter, light/dark toggle, animated counters, donation presets |
| Form validation | Student-ID format and intake year, university e-mail regex, required fields, dynamic error messages + summary |
| PHP processing | `process-join.php`: POST only, `trim` + `htmlspecialchars`, server-side validation, honeypot |
| Database | PDO, `CREATE TABLE IF NOT EXISTS`, prepared statements with `bindValue`, duplicate detection |
| Store **and retrieve** | `members.php` reads members back; the confirmation page shows the live member count |
| Fallback storage | `data/*.json` with file locking when MySQL is unavailable |
| Deployment | Relative paths, `.htaccess`, `Dockerfile` |

## Credits

- Base template: [TemplateMo 587 Tiya Golf Club](https://templatemo.com/tm-587-tiya-golf-club)
- Font: DM Sans, the template's typeface (SIL Open Font License). Loaded from Google Fonts with a self-hosted copy in `fonts/` for offline viewing
- Event photos (Innovation Expo 2026, TEXPO): R-RIC members
- Other photos: [Unsplash](https://unsplash.com) (free to use under the Unsplash License)
- Committee names and photos are placeholders. Replace them with your real committee.
