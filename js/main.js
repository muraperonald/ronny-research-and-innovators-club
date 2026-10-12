/* =====================================================================
   R-RIC · Ronny Research & Innovation Club
   js/main.js — shared script for all five pages

   Contents
     1. Theme (light/dark) applied immediately to avoid a flash
     2. DATA: arrays of objects (clubEvents, pastHighlights, publications, committeeMembers)
     3. Helpers (dates, formatting)
     4. Rendering: events list, home preview, next-event card, committee
     5. Widgets: event filter + search, gallery lightbox, theme toggle,
        back-to-top button, animated stat counters
     6. Form validation: Join form + Contact form
     7. Boot: everything is wired up on DOMContentLoaded

   This file is loaded in <head> (without defer) on purpose so that the
   saved theme is applied before the page paints. Everything that touches
   the page body waits for the DOMContentLoaded event.
   ===================================================================== */

"use strict";

/* ---------------------------------------------------------------------
   1. THEME: apply the saved choice before first paint
   --------------------------------------------------------------------- */
const THEME_KEY = "rric-theme";

function getSavedTheme() {
    try {
        return localStorage.getItem(THEME_KEY);
    } catch (err) {
        return null; // private mode / storage blocked
    }
}

function applyTheme(theme) {
    document.documentElement.setAttribute("data-theme", theme);
}

applyTheme(getSavedTheme() === "dark" ? "dark" : "light");


/* ---------------------------------------------------------------------
   2. DATA: arrays of objects
   --------------------------------------------------------------------- */

// Category metadata object: label + Bootstrap Icon for each event type.
const categoryInfo = {
    workshop:    { label: "Workshop",    icon: "bi-tools" },
    competition: { label: "Competition", icon: "bi-trophy" },
    meetup:      { label: "Meetup",      icon: "bi-people-fill" },
    talk:        { label: "Research Talk", icon: "bi-mic-fill" }
};

// Club events. Dates use ISO "YYYY-MM-DD" so they sort and compare cleanly.
const clubEvents = [
    {
        id: 1,
        title: "Freshers' Welcome Mixer",
        date: "2026-09-19",
        time: "4:00 PM – 7:00 PM",
        venue: "Student Centre Lawn",
        category: "meetup",
        image: "images/gallery/weekly-meetup.jpg",
        description: "Meet the committee, find a project team and hear what R-RIC has planned for the semester. Snacks and lightning demos included."
    },
    {
        id: 2,
        title: "Web Dev Crash Course: HTML, CSS & JavaScript",
        date: "2026-10-03",
        time: "9:00 AM – 1:00 PM",
        venue: "Computer Lab 3, ICT Building",
        category: "workshop",
        image: "images/gallery/pair-programming.jpg",
        description: "A hands-on morning where beginners build and publish their first responsive web page with HTML, CSS and a little JavaScript."
    },
    {
        id: 3,
        title: "Intro to Git & GitHub Workshop",
        date: "2026-10-17",
        time: "2:00 PM – 5:00 PM",
        venue: "Computer Lab 3, ICT Building",
        category: "workshop",
        image: "images/gallery/code-workshop.jpg",
        description: "Learn version control the way real teams use it: commits, branches, pull requests and resolving merge conflicts without panic."
    },
    {
        id: 4,
        title: "Research Paper Reading Circle",
        date: "2026-10-21",
        time: "4:00 PM – 6:00 PM",
        venue: "Innovation Hub, Room 204",
        category: "meetup",
        image: "images/gallery/library-study-group.jpg",
        description: "We pick one recent paper in AI, IoT or security, break it down together and brainstorm how a student team could build on it."
    },
    {
        id: 5,
        title: "Campus Hackathon 2026: Code for Community",
        date: "2026-11-07",
        time: "8:00 AM – 8:00 PM",
        venue: "Main Hall",
        category: "competition",
        image: "images/gallery/hackathon-tables.jpg",
        description: "A 12-hour team hackathon building tech solutions for local community challenges. Mentors on site and prizes for the top three teams."
    },
    {
        id: 6,
        title: "AI & Machine Learning Bootcamp (Python)",
        date: "2026-11-14",
        time: "9:00 AM – 4:00 PM",
        venue: "Computer Lab 1, ICT Building",
        category: "workshop",
        image: "images/gallery/python-bootcamp.jpg",
        description: "From NumPy to your first trained model. Bring a laptop with Python installed; notebooks are provided."
    },
    {
        id: 7,
        title: "Research Talk: Careers in Cybersecurity Research",
        date: "2026-11-20",
        time: "5:00 PM – 6:30 PM",
        venue: "Lecture Theatre B",
        category: "talk",
        image: "images/gallery/tech-talk-audience.jpg",
        description: "An industry panel on ethical hacking, SOC analyst roles and how to start building a security portfolio while still at university."
    },
    {
        id: 8,
        title: "Robotics & IoT Build Night",
        date: "2026-11-28",
        time: "3:00 PM – 9:00 PM",
        venue: "Engineering Workshop",
        category: "workshop",
        image: "images/gallery/robotics-expo.jpg",
        description: "Wire up sensors, program microcontrollers and build a small connected device. Kits are provided for registered members."
    },
    {
        id: 9,
        title: "Undergraduate Research & Innovation Symposium",
        date: "2026-12-05",
        time: "9:00 AM – 3:00 PM",
        venue: "Main Hall",
        category: "competition",
        image: "images/gallery/design-sprint.jpg",
        description: "Student teams present research posters and innovation pitches to a panel of lecturers and industry judges. Best project wins seed funding."
    },
    {
        id: 10,
        title: "End-of-Semester Demo Day",
        date: "2026-12-12",
        time: "2:00 PM – 6:00 PM",
        venue: "Main Hall",
        category: "meetup",
        image: "images/gallery/demo-day-stage.jpg",
        description: "Our showcase: project teams present to students, lecturers and invited industry guests, followed by the committee awards."
    },
    {
        id: 12,
        title: "Research Methods Workshop: Literature Reviews & Citations",
        date: "2026-10-28",
        time: "2:00 PM – 4:30 PM",
        venue: "Library Seminar Room",
        category: "workshop",
        image: "images/gallery/weekly-meetup.jpg",
        description: "Find, read and cite academic sources like a pro: Google Scholar, IEEE Xplore, Zotero and how to structure a literature review."
    },
    {
        id: 11,
        title: "Industry Visit: Inside a Data Centre",
        date: "2027-01-23",
        time: "9:00 AM – 1:00 PM",
        venue: "Meet at the Main Gate (bus provided)",
        category: "talk",
        image: "images/gallery/data-centre-visit.jpg",
        description: "A guided tour of a working data centre: networking, cooling, power and the people who keep the cloud running."
    }
];

// Past events & highlights with real club photos (events.html).
const pastHighlights = [
    {
        title: "Innovation Expo & Conference 2026",
        year: 2026,
        type: "Expo",
        image: "images/gallery/innovation-expo-2026-team.jpg",
        alt: "R-RIC members in formal wear standing together in front of the POTRAZ backdrop at the Innovation Expo 2026",
        summary: "R-RIC members represented the club at the Innovation Expo & Conference 2026, connecting with regulators, startups and fellow student innovators on the exhibition floor.",
        tags: ["Exhibition", "Networking"]
    },
    {
        title: "Innovation Expo 2026: Full Club Delegation",
        year: 2026,
        type: "Expo",
        image: "images/gallery/innovation-expo-2026-delegation.jpg",
        alt: "A larger group of R-RIC members and delegates posing together at the Innovation Expo 2026",
        summary: "Our largest delegation yet: members from several programmes attended the conference sessions together and shared what they learned at the next club meetup.",
        tags: ["Conference", "Teamwork"]
    },
    {
        title: "TEXPO: Live Prototype Demo",
        year: 2026,
        type: "Showcase",
        image: "images/gallery/texpo-prototype-demo.jpg",
        alt: "Club members in TEXPO T-shirts demonstrating a prototype on a laptop at their exhibition stand",
        summary: "A member project team ran a live demo of their prototype at TEXPO, walking visitors through the hardware and software behind it.",
        tags: ["Prototype", "Demo"]
    }
];

// Member publications & research outputs. Add real entries in this form:
// { title: "...", authors: "...", venue: "...", year: 2026, link: "https://..." }
const publications = [];

// Committee members, rendered as cards on about.html.
const committeeMembers = [
    {
        name: "Daniel Mensah",
        role: "Chairperson",
        course: "BSc. Computer Science, Year 3",
        image: "images/committee/chairperson.jpg",
        bio: "Leads the club's direction and research partnerships. Full-stack developer working on an AI-for-agriculture project.",
        links: { linkedin: "#", github: "#" }
    },
    {
        name: "Amina Yusuf",
        role: "Vice Chairperson",
        course: "BSc. Information Technology, Year 3",
        image: "images/committee/vice-chair.jpg",
        bio: "Runs member engagement and research mentorship pairing. Data science researcher in training.",
        links: { linkedin: "#", github: "#" }
    },
    {
        name: "Brian Moyo",
        role: "Secretary General",
        course: "BSc. Software Engineering, Year 2",
        image: "images/committee/secretary.jpg",
        bio: "Keeps the minutes, the calendar and the club website running. Ask him about JavaScript.",
        links: { linkedin: "#", github: "#" }
    },
    {
        name: "Grace Nyathi",
        role: "Treasurer",
        course: "BBIT, Year 2",
        image: "images/committee/treasurer.jpg",
        bio: "Manages the budget, sponsorships and membership fees. Fintech and blockchain enthusiast.",
        links: { linkedin: "#", github: "#" }
    },
    {
        name: "Faith Banda",
        role: "Events & Projects Lead",
        course: "BSc. Computer Science, Year 2",
        image: "images/committee/events-lead.jpg",
        bio: "Plans every workshop, hackathon and the annual symposium. Robotics and IoT tinkerer.",
        links: { linkedin: "#", github: "#" }
    },
    {
        name: "Dr. James Carter",
        role: "Faculty Patron",
        course: "Senior Lecturer, School of Computing",
        image: "images/committee/patron.jpg",
        bio: "Supervises club research projects and connects members with publication and industry opportunities.",
        links: { linkedin: "#" }
    }
];


/* ---------------------------------------------------------------------
   3. HELPERS
   --------------------------------------------------------------------- */
const MONTHS = ["Jan", "Feb", "Mar", "Apr", "May", "Jun",
                "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

// Parse "YYYY-MM-DD" as a *local* date (new Date("2026-10-17") would be UTC).
function parseDate(isoString) {
    const parts = isoString.split("-").map(Number);
    return new Date(parts[0], parts[1] - 1, parts[2]);
}

function startOfToday() {
    const now = new Date();
    return new Date(now.getFullYear(), now.getMonth(), now.getDate());
}

function isUpcoming(event) {
    return parseDate(event.date) >= startOfToday();
}

function daysUntil(event) {
    const msPerDay = 1000 * 60 * 60 * 24;
    return Math.round((parseDate(event.date) - startOfToday()) / msPerDay);
}

function sortByDate(events) {
    return [...events].sort((a, b) => parseDate(a.date) - parseDate(b.date));
}

function getUpcomingEvents() {
    return sortByDate(clubEvents.filter(isUpcoming));
}


/* ---------------------------------------------------------------------
   4. RENDERING (loops + template literals)
   --------------------------------------------------------------------- */

// Full event card (events.html). Reuses the template's .custom-block markup.
function eventCardHTML(event) {
    const d = parseDate(event.date);
    const cat = categoryInfo[event.category];
    const upcoming = isUpcoming(event);

    const action = upcoming
        ? `<a href="contact.html?event=${event.id}#join" class="btn custom-btn">RSVP &amp; Join</a>`
        : `<span class="event-status event-status-past"><i class="bi-check-circle-fill me-1"></i>Completed</span>`;

    return `
        <article class="row custom-block custom-block-bg event-card ${upcoming ? "" : "is-past"}" data-category="${event.category}">
            <div class="col-lg-2 col-md-4 col-12 order-2 order-md-0 order-lg-0">
                <div class="custom-block-date-wrap d-flex d-lg-block d-md-block align-items-center mt-3 mt-lg-0 mt-md-0">
                    <h6 class="custom-block-date mb-lg-1 mb-0 me-3 me-lg-0 me-md-0">${d.getDate()}</h6>
                    <strong class="text-white">${MONTHS[d.getMonth()]} ${d.getFullYear()}</strong>
                </div>
            </div>

            <div class="col-lg-4 col-md-8 col-12 order-1 order-lg-0">
                <div class="custom-block-image-wrap">
                    <img src="${event.image}" class="custom-block-image img-fluid" alt="${event.title}" loading="lazy">
                    <span class="event-badge event-badge-${event.category}"><i class="${cat.icon} me-1"></i>${cat.label}</span>
                </div>
            </div>

            <div class="col-lg-6 col-12 order-3 order-lg-0">
                <div class="custom-block-info mt-2 mt-lg-0">
                    <h3 class="events-title mb-3">${event.title}</h3>
                    <p class="mb-0">${event.description}</p>

                    <div class="d-flex flex-wrap border-top mt-4 pt-3">
                        <div class="mb-4 mb-lg-0">
                            <div class="d-flex flex-wrap align-items-center mb-1">
                                <span class="custom-block-span">Venue:</span>
                                <p class="mb-0">${event.venue}</p>
                            </div>
                            <div class="d-flex flex-wrap align-items-center">
                                <span class="custom-block-span">Time:</span>
                                <p class="mb-0">${event.time}</p>
                            </div>
                        </div>

                        <div class="d-flex align-items-center ms-lg-auto">${action}</div>
                    </div>
                </div>
            </div>
        </article>`;
}

// Compact card (index.html "Coming up" preview).
function eventPreviewHTML(event) {
    const d = parseDate(event.date);
    const cat = categoryInfo[event.category];

    return `
        <div class="col-lg-4 col-md-6 col-12 mb-4">
            <article class="event-preview h-100">
                <div class="custom-block-image-wrap">
                    <img src="${event.image}" class="custom-block-image img-fluid" alt="${event.title}" loading="lazy">
                    <span class="event-badge event-badge-${event.category}"><i class="${cat.icon} me-1"></i>${cat.label}</span>
                    <div class="custom-block-date-wrap">
                        <strong class="text-white">${d.getDate()} ${MONTHS[d.getMonth()]} ${d.getFullYear()}</strong>
                    </div>
                    <div class="custom-btn-wrap">
                        <a href="contact.html?event=${event.id}#join" class="btn custom-btn">RSVP</a>
                    </div>
                </div>
                <div class="custom-block-info">
                    <h3 class="h5 mb-2">${event.title}</h3>
                    <p class="mb-0 small"><i class="bi-geo-alt-fill me-1"></i>${event.venue}</p>
                </div>
            </article>
        </div>`;
}

// Renders a list of events into a container using a loop.
function renderEvents(events, container) {
    let html = "";
    for (let i = 0; i < events.length; i++) {
        html += eventCardHTML(events[i]);
    }
    container.innerHTML = html;
}

function renderHomePreview(container) {
    const nextThree = getUpcomingEvents().slice(0, 3);
    if (nextThree.length === 0) {
        container.innerHTML = `<p class="text-center">New events are being planned. Check back soon!</p>`;
        return;
    }
    container.innerHTML = nextThree.map(eventPreviewHTML).join("");
}

function renderNextEventCard(container) {
    const next = getUpcomingEvents()[0];
    if (!next) {
        container.innerHTML = `<p class="mb-0">No upcoming events right now. Follow us for announcements.</p>`;
        return;
    }

    const days = daysUntil(next);
    let countdown;
    if (days === 0) {
        countdown = "Happening today!";
    } else if (days === 1) {
        countdown = "Tomorrow";
    } else {
        countdown = `In ${days} days`;
    }

    const d = parseDate(next.date);
    container.innerHTML = `
        <span class="next-event-label"><i class="bi-calendar-event me-2"></i>Next up · ${countdown}</span>
        <h3 class="text-white mt-3 mb-2">${next.title}</h3>
        <p class="text-white-50 mb-3">${d.getDate()} ${MONTHS[d.getMonth()]} ${d.getFullYear()} · ${next.time}<br>${next.venue}</p>
        <a href="contact.html?event=${next.id}#join" class="btn custom-btn me-2">Reserve a seat</a>
        <a href="events.html" class="link">All events <i class="bi-arrow-right"></i></a>`;
}

function renderCommittee(container) {
    let html = "";
    committeeMembers.forEach((member) => {
        let socialLinks = "";
        for (const network in member.links) {
            socialLinks += `
                <li class="social-icon-item">
                    <a href="${member.links[network]}" class="social-icon-link bi-${network}" aria-label="${member.name} on ${network}"></a>
                </li>`;
        }

        html += `
            <div class="col-lg-4 col-md-6 col-12 mb-4">
                <article class="member-block committee-card">
                    <div class="member-block-image-wrap">
                        <img src="${member.image}" class="member-block-image img-fluid" alt="Portrait of ${member.name}, ${member.role}" loading="lazy">
                        <p class="committee-bio">${member.bio}</p>
                        <ul class="social-icon">${socialLinks}</ul>
                    </div>
                    <div class="member-block-info">
                        <div class="d-flex align-items-center">
                            <h4 class="h5">${member.name}</h4>
                            <p class="ms-auto committee-role">${member.role}</p>
                        </div>
                        <small class="text-muted">${member.course}</small>
                    </div>
                </article>
            </div>`;
    });
    container.innerHTML = html;

    // Explicit onmouseover / onmouseout events: reveal the bio overlay.
    container.querySelectorAll(".committee-card").forEach((card) => {
        card.onmouseover = function () {
            card.classList.add("show-bio");
        };
        card.onmouseout = function (e) {
            // Ignore moves between child elements inside the same card.
            if (!card.contains(e.relatedTarget)) {
                card.classList.remove("show-bio");
            }
        };
    });
}


function renderHighlights(container) {
    let html = "";
    for (const item of pastHighlights) {
        const tags = item.tags.map((tag) => `<li>${tag}</li>`).join("");
        html += `
            <div class="col-lg-4 col-md-6 col-12 mb-4">
                <article class="highlight-story h-100">
                    <a href="${item.image}" class="highlight-story-image" aria-label="Open full photo: ${item.title}">
                        <img src="${item.image}" class="img-fluid" alt="${item.alt}" loading="lazy">
                        <span class="event-badge event-badge-competition">${item.type} · ${item.year}</span>
                    </a>
                    <div class="highlight-story-body">
                        <h3 class="h5 mb-2">${item.title}</h3>
                        <p class="mb-3">${item.summary}</p>
                        <ul class="tag-list">${tags}</ul>
                    </div>
                </article>
            </div>`;
    }
    container.innerHTML = html;
}

function renderPublications(container) {
    if (publications.length === 0) {
        container.innerHTML = `
            <div class="empty-state text-center">
                <i class="bi-journal-text display-6 d-block mb-3"></i>
                <p class="mb-2">Member papers, posters and research reports will be listed here.</p>
                <p class="mb-0 small">Published something? <a href="contact.html#message">Tell the committee</a> and we will add it.</p>
            </div>`;
        return;
    }

    let html = '<ol class="publication-list">';
    publications.forEach((pub) => {
        const title = pub.link ? `<a href="${pub.link}" target="_blank" rel="noopener">${pub.title}</a>` : pub.title;
        html += `<li><strong>${title}</strong><br><span>${pub.authors}</span> · <em>${pub.venue}</em>, ${pub.year}</li>`;
    });
    container.innerHTML = html + "</ol>";
}


/* ---------------------------------------------------------------------
   5. WIDGETS
   --------------------------------------------------------------------- */

// 5a. Event category filter + search (events.html)
function initEventFilter() {
    const list = document.getElementById("eventsList");
    const filterBar = document.getElementById("eventFilters");
    const search = document.getElementById("eventSearch");
    const upcomingOnly = document.getElementById("upcomingOnly");
    const counter = document.getElementById("eventCount");
    if (!list || !filterBar) return;

    // State object for the widget.
    const state = { category: "all", term: "", upcomingOnly: false };

    function update() {
        const term = state.term.toLowerCase();

        const visible = sortByDate(clubEvents).filter((event) => {
            const matchesCategory = state.category === "all" || event.category === state.category;
            const matchesTerm = term === "" ||
                event.title.toLowerCase().includes(term) ||
                event.venue.toLowerCase().includes(term) ||
                event.description.toLowerCase().includes(term);
            const matchesDate = !state.upcomingOnly || isUpcoming(event);
            return matchesCategory && matchesTerm && matchesDate;
        });

        if (visible.length > 0) {
            renderEvents(visible, list);
        } else {
            list.innerHTML = `
                <div class="empty-state text-center">
                    <i class="bi-search display-6 d-block mb-3"></i>
                    <p class="mb-0">No events match your filters. Try another category or search term.</p>
                </div>`;
        }

        const upcomingCount = visible.filter(isUpcoming).length;
        counter.textContent = `Showing ${visible.length} of ${clubEvents.length} events · ${upcomingCount} upcoming`;
    }

    // One click listener on the bar handles every filter button (event delegation).
    filterBar.addEventListener("click", (e) => {
        const button = e.target.closest("button[data-filter]");
        if (!button) return;

        filterBar.querySelectorAll("button[data-filter]").forEach((b) => {
            b.classList.remove("active");
            b.setAttribute("aria-pressed", "false");
        });
        button.classList.add("active");
        button.setAttribute("aria-pressed", "true");

        state.category = button.dataset.filter;
        update();
    });

    if (search) {
        search.addEventListener("input", () => {
            state.term = search.value.trim();
            update();
        });
    }

    if (upcomingOnly) {
        upcomingOnly.addEventListener("change", () => {
            state.upcomingOnly = upcomingOnly.checked;
            update();
        });
    }

    // Allow deep links such as events.html?category=workshop
    const fromUrl = new URLSearchParams(window.location.search).get("category");
    if (fromUrl && categoryInfo[fromUrl]) {
        const btn = filterBar.querySelector(`button[data-filter="${fromUrl}"]`);
        if (btn) btn.click();
        return;
    }

    update();
}

// 5b. Gallery lightbox (gallery.html)
function initLightbox() {
    const grid = document.getElementById("galleryGrid");
    const box = document.getElementById("lightbox");
    if (!grid || !box) return;

    const img = box.querySelector(".lightbox-image");
    const caption = box.querySelector(".lightbox-caption");
    const counter = box.querySelector(".lightbox-counter");
    const items = Array.from(grid.querySelectorAll(".gallery-item"));
    let current = 0;
    let lastFocus = null;

    function show(index) {
        // Wrap around at both ends.
        current = (index + items.length) % items.length;
        const item = items[current];
        img.src = item.getAttribute("href");
        img.alt = item.querySelector("img").alt;
        caption.textContent = item.dataset.caption;
        counter.textContent = `${current + 1} / ${items.length}`;
    }

    function open(index) {
        lastFocus = document.activeElement;
        show(index);
        box.hidden = false;
        document.body.classList.add("lightbox-open");
        box.querySelector(".lightbox-close").focus();
    }

    function close() {
        box.hidden = true;
        document.body.classList.remove("lightbox-open");
        if (lastFocus) lastFocus.focus();
    }

    items.forEach((item, index) => {
        item.addEventListener("click", (e) => {
            e.preventDefault(); // without JS the link simply opens the full image
            open(index);
        });
    });

    box.querySelector(".lightbox-close").onclick = close;
    box.querySelector(".lightbox-prev").onclick = () => show(current - 1);
    box.querySelector(".lightbox-next").onclick = () => show(current + 1);

    // Click on the dark backdrop (not the image) closes the lightbox.
    box.addEventListener("click", (e) => {
        if (e.target === box) close();
    });

    document.addEventListener("keydown", (e) => {
        if (box.hidden) return;
        if (e.key === "Escape") close();
        else if (e.key === "ArrowRight") show(current + 1);
        else if (e.key === "ArrowLeft") show(current - 1);
    });

    // Gallery category filter (All / Workshops / Hackathons / ...)
    const filters = document.getElementById("galleryFilters");
    if (filters) {
        filters.addEventListener("click", (e) => {
            const button = e.target.closest("button[data-filter]");
            if (!button) return;
            filters.querySelectorAll("button").forEach((b) => b.classList.remove("active"));
            button.classList.add("active");

            const wanted = button.dataset.filter;
            grid.querySelectorAll(".gallery-card").forEach((card) => {
                const show = wanted === "all" || card.dataset.category === wanted;
                card.classList.toggle("is-hidden", !show);
            });
        });

        // Deep links such as gallery.html?filter=expo
        const wanted = new URLSearchParams(window.location.search).get("filter");
        const preset = wanted && filters.querySelector(`button[data-filter="${CSS.escape(wanted)}"]`);
        if (preset) preset.click();
    }
}

// 5c. Light / dark theme toggle (every page)
function initThemeToggle() {
    const toggle = document.getElementById("themeToggle");
    if (!toggle) return;

    function syncButton() {
        const dark = document.documentElement.getAttribute("data-theme") === "dark";
        toggle.innerHTML = dark ? '<i class="bi-sun-fill"></i>' : '<i class="bi-moon-stars-fill"></i>';
        toggle.setAttribute("aria-label", dark ? "Switch to light mode" : "Switch to dark mode");
        toggle.setAttribute("aria-pressed", String(dark));
    }

    // Explicit onclick handler.
    toggle.onclick = function () {
        const next = document.documentElement.getAttribute("data-theme") === "dark" ? "light" : "dark";
        applyTheme(next);
        try {
            localStorage.setItem(THEME_KEY, next);
        } catch (err) {
            /* storage unavailable: the theme still applies for this page view */
        }
        syncButton();
    };

    syncButton();
}

// 5d. Back-to-top button (fixed position, appears after scrolling)
function initBackToTop() {
    const button = document.getElementById("backToTop");
    if (!button) return;

    window.addEventListener("scroll", () => {
        button.classList.toggle("is-visible", window.scrollY > 400);
    }, { passive: true });

    button.onclick = () => window.scrollTo({ top: 0, behavior: "smooth" });
}

// 5e. Animated number counters (index.html stats)
function initCounters() {
    const counters = document.querySelectorAll("[data-count]");
    if (counters.length === 0) return;

    function animate(el) {
        const target = Number(el.dataset.count);
        const suffix = el.dataset.suffix || "";
        const duration = 1200;
        const start = performance.now();

        function step(now) {
            const progress = Math.min((now - start) / duration, 1);
            el.textContent = Math.floor(progress * target) + suffix;
            if (progress < 1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
    }

    if (!("IntersectionObserver" in window)) {
        counters.forEach(animate);
        return;
    }

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                animate(entry.target);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.6 });

    counters.forEach((el) => observer.observe(el));
}

// 5f. Close the mobile menu after a link is tapped
function initMobileNav() {
    const collapseEl = document.getElementById("navbarNav");
    if (!collapseEl || typeof bootstrap === "undefined") return;

    collapseEl.querySelectorAll(".nav-link").forEach((link) => {
        link.addEventListener("click", () => {
            if (collapseEl.classList.contains("show")) {
                bootstrap.Collapse.getOrCreateInstance(collapseEl).hide();
            }
        });
    });
}


/* ---------------------------------------------------------------------
   6. FORM VALIDATION (Join, Contact and Donation forms)
   The same rules are enforced again on the server in process-join.php.
   --------------------------------------------------------------------- */

// Student ID: PROGRAMME / INTAKE-YEAR / SERIAL, e.g. ICS/2024/0123
const STUDENT_ID_PATTERN = /^[A-Z]{2,4}\/(\d{4})\/\d{3,5}$/;

// University e-mail: any address on an academic domain (.ac.xx or .edu / .edu.xx),
// e.g. jane.doe@students.university.ac.ke or jdoe@college.edu
const UNI_EMAIL_PATTERN = /^[A-Za-z0-9._%+-]+@(?:[A-Za-z0-9-]+\.)+(?:ac\.[A-Za-z]{2}|edu(?:\.[A-Za-z]{2})?)$/;

const EMAIL_PATTERN = /^[A-Za-z0-9._%+-]+@(?:[A-Za-z0-9-]+\.)+[A-Za-z]{2,}$/;
const NAME_PATTERN = /^[A-Za-z][A-Za-z .'-]{1,58}[A-Za-z.]$/;
const PHONE_PATTERN = /^\+?\d[\d\s-]{7,15}\d$/;

// Show an error under a field, creating the message element on first use.
function showError(field, message) {
    const wrapper = field.closest(".field");
    let error = wrapper.querySelector(".field-error");

    if (!error) {
        error = document.createElement("div");
        error.className = "field-error";
        error.id = `${field.name || field.id}-error`;
        error.setAttribute("aria-live", "polite");
        wrapper.appendChild(error);
    }

    error.innerHTML = `<i class="bi-exclamation-triangle-fill me-1"></i>${message}`;
    wrapper.classList.add("has-error");
    wrapper.classList.remove("is-ok");
    field.setAttribute("aria-invalid", "true");
    field.setAttribute("aria-describedby", error.id);
}

function clearError(field) {
    const wrapper = field.closest(".field");
    const error = wrapper.querySelector(".field-error");
    if (error) error.remove();
    wrapper.classList.remove("has-error");
    wrapper.classList.add("is-ok");
    field.removeAttribute("aria-invalid");
    field.removeAttribute("aria-describedby");
}

// Rule set for the Join form. Each validator returns "" when valid,
// otherwise the error message to display.
const joinRules = {
    full_name(value) {
        if (value === "") return "Please enter your full name.";
        if (value.split(/\s+/).length < 2) return "Please enter both your first and last name.";
        if (!NAME_PATTERN.test(value)) return "Names may only contain letters, spaces, apostrophes, dots and hyphens.";
        return "";
    },
    student_id(value) {
        if (value === "") return "Your student ID is required.";
        const match = value.match(STUDENT_ID_PATTERN);
        if (!match) return "Use the format PROGRAMME/YEAR/NUMBER, e.g. ICS/2024/0123.";
        const intakeYear = Number(match[1]);
        const thisYear = new Date().getFullYear();
        if (intakeYear < thisYear - 8 || intakeYear > thisYear) {
            return `The intake year must be between ${thisYear - 8} and ${thisYear}.`;
        }
        return "";
    },
    email(value) {
        if (value === "") return "Your university e-mail address is required.";
        if (!UNI_EMAIL_PATTERN.test(value)) {
            return "Use your university e-mail (ending in .ac.xx or .edu), not a personal address.";
        }
        return "";
    },
    phone(value) {
        if (value !== "" && !PHONE_PATTERN.test(value)) return "Enter a valid phone number, e.g. +263 712 345 678.";
        return "";
    },
    course(value) {
        return value === "" ? "Please choose your programme." : "";
    },
    year_of_study(value) {
        return value === "" ? "Please choose your year of study." : "";
    },
    motivation(value) {
        return value.length > 500 ? `Please keep this under 500 characters (currently ${value.length}).` : "";
    }
};

const contactRules = {
    full_name: joinRules.full_name,
    email(value) {
        if (value === "") return "Your e-mail address is required so we can reply.";
        if (!EMAIL_PATTERN.test(value)) return "Please enter a valid e-mail address.";
        return "";
    },
    subject(value) {
        return value === "" ? "Please choose a subject." : "";
    },
    message(value) {
        if (value.length < 20) return `Your message is too short (${value.length}/20 characters minimum).`;
        if (value.length > 1000) return "Please keep your message under 1000 characters.";
        return "";
    }
};

// Validate a single text/select field against a rule set.
function validateField(field, rules) {
    const rule = rules[field.name];
    if (!rule) return true;

    const message = rule(field.value.trim());
    if (message) {
        showError(field, message);
        return false;
    }
    clearError(field);
    return true;
}

// Validate a group (radio buttons / checkboxes) by name.
function validateGroup(form, name, message) {
    const inputs = form.querySelectorAll(`input[name="${name}"]`);
    const anyChecked = Array.from(inputs).some((input) => input.checked);
    if (!anyChecked) {
        showError(inputs[0], message);
        return false;
    }
    clearError(inputs[0]);
    return true;
}

// Shows (or clears) the summary box at the top of the form.
function showSummary(form, errorCount) {
    const summary = form.querySelector(".form-summary");
    if (!summary) return;

    if (errorCount === 0) {
        summary.hidden = true;
        return;
    }
    summary.hidden = false;
    summary.innerHTML = `<i class="bi-exclamation-triangle-fill me-2"></i>
        Please fix the ${errorCount} highlighted ${errorCount === 1 ? "field" : "fields"} below.`;
    summary.focus();
}

function initJoinForm() {
    const form = document.getElementById("joinForm");
    if (!form) return;

    const studentId = form.elements["student_id"];
    const motivation = form.elements["motivation"];
    const charCount = document.getElementById("motivationCount");

    // Auto-uppercase the student ID as the user types.
    studentId.addEventListener("input", () => {
        studentId.value = studentId.value.toUpperCase();
    });

    // Live character counter.
    if (motivation && charCount) {
        const updateCount = () => {
            const length = motivation.value.length;
            charCount.textContent = `${length} / 500`;
            charCount.classList.toggle("text-danger", length > 500);
        };
        motivation.addEventListener("input", updateCount);
        updateCount();
    }

    // Pre-select the event from links like contact.html?event=5#join
    const params = new URLSearchParams(window.location.search);
    const eventId = Number(params.get("event"));
    const chosen = clubEvents.find((e) => e.id === eventId);
    const eventNote = document.getElementById("rsvpNote");
    if (chosen && eventNote) {
        form.elements["event_interest"].value = chosen.title;
        eventNote.hidden = false;
        eventNote.querySelector("strong").textContent = chosen.title;
    }

    // Pre-select a tier from links like contact.html?tier=builder#join
    const tier = params.get("tier");
    if (tier) {
        const radio = form.querySelector(`input[name="tier"][value="${CSS.escape(tier)}"]`);
        if (radio) radio.checked = true;
    }

    // Re-validate a field when the user leaves it, and live once it has an error.
    form.querySelectorAll("input, select, textarea").forEach((field) => {
        field.addEventListener("blur", () => {
            if (field.value.trim() !== "" || field.closest(".has-error")) {
                validateField(field, joinRules);
            }
        });
        field.addEventListener("input", () => {
            if (field.closest(".has-error")) validateField(field, joinRules);
        });
    });

    form.addEventListener("change", (e) => {
        if (e.target.name === "tier") validateGroup(form, "tier", "Choose a membership tier.");
        if (e.target.name === "interests[]") validateGroup(form, "interests[]", "Pick at least one area of interest.");
        if (e.target.name === "agree") validateAgree();
    });

    function validateAgree() {
        const agree = form.elements["agree"];
        if (!agree.checked) {
            showError(agree, "You must agree to the club code of conduct.");
            return false;
        }
        clearError(agree);
        return true;
    }

    form.addEventListener("submit", (e) => {
        let errors = 0;

        ["full_name", "student_id", "email", "phone", "course", "year_of_study", "motivation"].forEach((name) => {
            if (!validateField(form.elements[name], joinRules)) errors++;
        });
        if (!validateGroup(form, "tier", "Choose a membership tier.")) errors++;
        if (!validateGroup(form, "interests[]", "Pick at least one area of interest.")) errors++;
        if (!validateAgree()) errors++;

        if (errors > 0) {
            e.preventDefault();
            showSummary(form, errors);
            const firstError = form.querySelector(".has-error input, .has-error select, .has-error textarea");
            if (firstError) firstError.focus();
            return;
        }

        showSummary(form, 0);
        const submit = form.querySelector('button[type="submit"]');
        submit.disabled = true;
        submit.textContent = "Submitting…";
    });
}

function initContactForm() {
    const form = document.getElementById("contactForm");
    if (!form) return;

    form.querySelectorAll("input, select, textarea").forEach((field) => {
        field.addEventListener("blur", () => {
            if (field.value.trim() !== "" || field.closest(".has-error")) {
                validateField(field, contactRules);
            }
        });
        field.addEventListener("input", () => {
            if (field.closest(".has-error")) validateField(field, contactRules);
        });
    });

    form.addEventListener("submit", (e) => {
        let errors = 0;
        ["full_name", "email", "subject", "message"].forEach((name) => {
            if (!validateField(form.elements[name], contactRules)) errors++;
        });

        if (errors > 0) {
            e.preventDefault();
            showSummary(form, errors);
            return;
        }

        showSummary(form, 0);
        const submit = form.querySelector('button[type="submit"]');
        submit.disabled = true;
        submit.textContent = "Sending…";
    });
}


// Donation pledge form (support.html)
const donationRules = {
    amount(value) {
        const amount = Number(value);
        if (value === "") return "Please enter an amount.";
        if (!Number.isInteger(amount) || amount < 1) return "Enter a whole number of at least $1.";
        if (amount > 10000) return "For pledges above $10,000 please e-mail the committee.";
        return "";
    },
    fund(value) {
        return value === "" ? "Please choose a fund." : "";
    },
    full_name: joinRules.full_name,
    email: contactRules.email,
    payment_method(value) {
        return value === "" ? "Please choose how you would like to pay." : "";
    },
    message(value) {
        return value.length > 300 ? "Please keep your message under 300 characters." : "";
    }
};

function initDonationForm() {
    const form = document.getElementById("donationForm");
    if (!form) return;

    const presets = document.getElementById("amountPresets");
    const amountInput = form.elements["amount"];
    const submit = document.getElementById("donateSubmit");

    function syncAmount() {
        const amount = Number(amountInput.value);
        submit.textContent = amount >= 1 ? `Pledge $${amount}` : "Pledge";

        // Highlight the preset that matches the typed amount, if any.
        presets.querySelectorAll(".amount-btn").forEach((btn) => {
            btn.classList.toggle("active", Number(btn.dataset.amount) === amount);
        });
    }

    presets.addEventListener("click", (e) => {
        const button = e.target.closest(".amount-btn");
        if (!button) return;
        amountInput.value = button.dataset.amount;
        syncAmount();
        validateField(amountInput, donationRules);
    });

    amountInput.addEventListener("input", syncAmount);

    form.querySelectorAll("input, select, textarea").forEach((field) => {
        field.addEventListener("blur", () => {
            if (field.value.trim() !== "" || field.closest(".has-error")) {
                validateField(field, donationRules);
            }
        });
    });

    form.addEventListener("submit", (e) => {
        let errors = 0;
        ["amount", "fund", "full_name", "email", "payment_method", "message"].forEach((name) => {
            if (!validateField(form.elements[name], donationRules)) errors++;
        });

        if (errors > 0) {
            e.preventDefault();
            showSummary(form, errors);
            return;
        }

        showSummary(form, 0);
        submit.disabled = true;
        submit.textContent = "Sending pledge…";
    });

    syncAmount();
}


/* ---------------------------------------------------------------------
   7. BOOT
   --------------------------------------------------------------------- */
document.addEventListener("DOMContentLoaded", () => {
    // Shared on every page
    initThemeToggle();
    initBackToTop();
    initMobileNav();

    const year = document.getElementById("year");
    if (year) year.textContent = new Date().getFullYear();

    // Page-specific features: each init function returns early when its
    // elements are not on the current page.
    const nextEventCard = document.getElementById("nextEventCard");
    if (nextEventCard) renderNextEventCard(nextEventCard);

    const homeEvents = document.getElementById("homeEvents");
    if (homeEvents) renderHomePreview(homeEvents);

    const committeeGrid = document.getElementById("committeeGrid");
    if (committeeGrid) renderCommittee(committeeGrid);

    const highlightsGrid = document.getElementById("pastHighlights");
    if (highlightsGrid) renderHighlights(highlightsGrid);

    const publicationList = document.getElementById("publicationList");
    if (publicationList) renderPublications(publicationList);

    initCounters();
    initEventFilter();
    initLightbox();
    initJoinForm();
    initContactForm();
    initDonationForm();
});
