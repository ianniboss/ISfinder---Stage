---
trigger: always_on
---

# Internship Project Rules — ISfinder / ISadmin Migration (PHP 8.5)

## General Context
- Project: Migration and maintenance of the legacy ISfinder / ISadmin PHP applications
- Environment:
  - External server (.36): ISfinder public website
  - Internal server (.150): ISadmin internal administration website
- Main objective:
  - Ensure compatibility with PHP 8.5
  - Preserve existing behavior and database structure
  - Avoid unnecessary rewrites

---

# CORE RULES

## 1. NEVER rewrite the whole application
- Do NOT redesign the architecture
- Do NOT modernize everything
- Do NOT replace all legacy code unnecessarily
- Work incrementally and safely

---

## 2. PRESERVE EXISTING BEHAVIOR
- Existing workflows must continue to function exactly the same
- Frontend appearance/layout must remain unchanged unless explicitly requested
- Existing SQL/database structure must remain compatible

---

## 3. PRIORITIZE SAFE MODIFICATIONS
Prefer:
- small isolated fixes
- compatibility patches
- minimal-risk updates

Avoid:
- massive refactors
- changing core shared functions globally
- modifying stable working logic

---

# PHP 8.5 MIGRATION RULES

## Acceptable legacy features
The following are still acceptable if working:
- `mysqli`
- `mysqli_query`
- `mysqli_real_escape_string`
- variable variables (`$$var`)
- `mail()`
- `include`
- procedural PHP style

Do NOT rewrite them unless:
- they break in PHP 8.5
- they produce warnings/errors
- Patricia explicitly requests modernization

---

## Things to watch carefully
- `match()` compatibility
- deprecated warnings
- headers already sent
- undefined variables
- session handling
- captcha/session interactions
- redirects using `header()`

---

# DEBUGGING RULES

## Always debug incrementally
- Change ONE thing at a time
- Test immediately after each modification

---
---

# COMMIT MESSAGE

## write conventional commits
- for example, docs: ...
- feat:...
- fix :...

---

## Always verify:
- frontend behavior
- database insertion
- logs (`/var/log/httpd/error_log`)
- session persistence
- dynamic form behavior (ORF / insertion sites)

---

## Keep logs open during testing
Use:
```bash
tail -f /var/log/httpd/error_log
````

---

# DATABASE RULES

## Preserve relational integrity

Critical tables:

* `submiters`
* `submission`
* `element_transposable`
* `orf`
* `host`
* `et_insertion_site`
* `request_names`

Always verify:

* inserts succeed
* foreign keys remain coherent
* IDs are linked correctly

---

# CAPTCHA RULES

## Captcha integration must:

* validate BEFORE DB insertion
* preserve form data after failure
* not break dynamic ORF behavior
* use session-based validation
* follow same pattern as existing forms

---

# UI / UX RULES

## Preserve user experience

* keep form data after validation errors
* display clear error messages
* avoid blank pages / HTTP 500
* avoid behavior regressions

---

# WORKING METHOD

## Before modifying a script:

1. Understand the data flow
2. Identify related files
3. Check includes/dependencies
4. Test existing behavior first

---

## After modifying:

1. Test functionality
2. Verify logs
3. Verify database
4. Test edge cases
5. Confirm no regression
6. Add comments as to why the changes are made so that its easy to understand (anyone who sees the code can know why)

---

# COMMUNICATION STYLE

When analyzing:

* explain causes clearly
* distinguish:

  * real bugs
  * environment-dependent behavior
  * legacy but acceptable code
* prioritize practical solutions over theoretical perfection

---

# IMPORTANT MINDSET

Goal is NOT:

* to create a modern framework
* to make perfect code

Goal IS:

* stable migration
* compatibility
* reliability
* preserving functionality
* safe incremental improvement

## NATURAL WRITING INSTRUCTIONS

Write using these rules to sound completely human:

### LANGUAGE RULES

- Simple words: Write like you talk to a friend, avoid complex vocabulary

- Short sentences: Break up complex thoughts into digestible pieces

- No AI phrases: Never use "dive into," "unleash," "game-changing," "revolutionary," "transformative," "leverage," "optimize," "unlock potential"

- Be direct: Say what you mean without unnecessary words

- Natural flow: It's fine to start sentences with "and," "but," or "so"

- Real voice: Don't force friendliness or fake excitement

### STYLE IMPLEMENTATION

- Keep grammar conversational: Simple sentence structures, not academic writing

- Cut fluff: Remove unnecessary adjectives and adverbs

- Use examples: Show with specific cases instead of abstract concepts

- Be honest: Admit limitations, don't oversell or hype

- Write like texting: Casual, direct, how you'd actually communicate

- Natural transitions: Use simple connectors like "here's the thing," "and," "but"

### AVOID THESE AI GIVEAWAYS

- "Let's dive into..."

- "Unleash your potential"

- "Game-changing solution"

- "Revolutionary approach"

- "Transform your life"

- "Unlock the secrets"

- "Leverage this strategy"

- "Optimize your workflow"

### USE THESE INSTEAD

- "Here's how it works"

- "This can help you"

- "Here's what I found"

- "This might work for you"

- "Here's the thing"

- "And that's why it matters"

- "But here's the problem"

- "So here's what happened"

### FINAL CHECK

Before finishing, ensure the writing:

- Sounds like something you'd say out loud

- Uses words a normal person would use

- Doesn't sound like marketing copy

- Feels genuine and honest

- Gets to the point quickly