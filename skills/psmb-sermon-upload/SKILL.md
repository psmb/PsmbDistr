---
name: psmb-sermon-upload
description: Draft and publish sermons in the psmb.ru Neos CMS. Use for sermon uploads from a transcript or document, including liturgical calendar dates and feasts, Bible readings, author tags, and themes.
---

# Upload a sermon in Neos

## Agree on the links first

Read the source and identify the title, body, author, delivered date if known, and the central reading or occasion. Use the source's wording and paragraph breaks. For Google Docs, use a connected document reader when available.

Before creating or editing any Neos node, show the proposed author, themes, and calendar links together and get the user's confirmation. Read-only lookups in Neos and the calendar app can help prepare the proposal.

- **Автор** is required. **ОГК** means **свящ. Георгий Кочетков**. The existing author tag is **Священник Георгий Кочетков**. Reuse an existing author tag, checking surname and name variants before proposing a new one. If missing, confirm the canonical label and create the tag through the sermon author picker after approval.
- **Темы** are references to the existing theme vocabulary. Propose a small set supported by the sermon, confirm them with the user, and select the matching tags. Ask about a missing theme before creating a tag or substituting another.
- **Дата** is for a recurring calendar date or feast. The date a sermon was delivered in the source is context. Confirm whether that date or occasion should drive recurring placement and confirm the exact code.
- **Отрывок** is for a matching Bible reading. Copy the complete **зачало** label exactly as the calendar app uses it, including punctuation, spaces, Roman numerals, and verse limits. A cross-reference in the body or a shorter book/chapter/verse citation is insufficient. If the sermon discusses several passages, ask which exact app reading should drive the link.
- Confirm whether to use the date, the reading, or both. When both are set, either match can surface the sermon. Leave unused fields empty. See [calendar keys and exact labels](references/neos.md#calendar-links).

## Create, verify, and request review

1. Open [Neos](https://psmb.ru/neos). When the environment provides a shared browser preview, use it with the authenticated profile if available. Sign-in placeholders are `<NEOS_EMAIL>` and `<NEOS_PASSWORD>`. Use credentials provided for this session or a secure sign-in handoff. Keep credentials out of files and reports.
2. Confirm the target workspace is personal and the publish destination is live. Keep **Auto-Publish** off. In the **Document Tree**, expand **Проповеди**, select an existing child sermon, and use the tree's **Create new → Insert after → Календарь → Календарь: Проповедь**. Enter the title in the creation dialog. Verify the new page is a child of **Проповеди** (internal path `/sites/site/s/…`). Check for the same sermon or an earlier draft before creating a duplicate.
3. Paste the body into the existing inline text element. Fill the optional subtitle only when supplied or approved. In **Атрибуты проповеди**, select the agreed **Автор** and **Темы**, enter the agreed **Дата** and **Отрывок**, and click inspector **Apply**. See [editor and workspace controls](references/neos.md#editor-and-workspace-controls).
4. Wait for saves, reopen the draft, and compare the persisted title, body, author, themes, and both calendar fields with the source and approved links. Verify the draft is pending in the personal workspace. If an action times out, check the tree and workspace before retrying creation or saving.
5. Share the Neos draft URL and a concise summary of the links. Ask the user to open the draft and confirm the review. If requested changes affect the approved draft, save and verify them, then ask the user to review the revised draft.

## Publish only after a separate confirmation

Once the user confirms the draft review, ask a separate explicit question about publishing that specific sermon to live. Wait for a new affirmative response to that question. Draft review approval and an earlier general request to upload do not authorize publication.

Open **Review changes** from the publish dropdown. In **Workspaces**, select only the sermon and its content, plus any approved new author or theme tags it references. Use **Publish selected changes** to send those changes to **live**. If the approved changes cannot be isolated, stop and explain the blocker. Verify the live page and its author, themes, and intended calendar placement. Report the live URL.

For an authorized disposable test, give the draft a unique test title. Use the publish dropdown's scoped **Discard** after verification and confirm the test page and any test-created dependencies are gone. The inspector's **Discard** only resets unapplied field edits. Keep unrelated workspace changes intact.
