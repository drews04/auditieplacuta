# HEADER DEBUG INFO

## Current State:
- Header class: `transparent-header`
- Position: `relative`
- Z-index: `9999`
- Background: `transparent`
- Padding: `25px 0`

## Banner State:
- Class: `.sc-banner`
- Padding-top: `60px` (changed from 170px)
- Position: `relative`
- Z-index: `9`

## Leaderboard Section:
- Padding-top: `pt-8 md:pt-12` (changed from pt-28 md:pt-36)

## Issues Found:
1. Header has `transparent` background
2. Banner starts immediately after header (relative positioning)
3. Total space before content: header (25px * 2) + banner (60px) = 110px top space

## Hypothesis:
The `transparent` background on the header makes it look like content is overlapping when it's actually the banner background showing through the header.

## Solution Needed:
Make the header have a solid background OR add more padding to banner OR add margin-top to the main content area.

