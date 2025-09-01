# 🎵 Concurs Day Simulator

A comprehensive console command for simulating a full weekday cycle of the Concurs music contest system.

## 🚀 Usage

### Basic Simulation
```bash
php artisan concurs:simulate-day
```

### Custom Parameters
```bash
# Create 5 users and 8 songs
php artisan concurs:simulate-day --users=5 --songs=8

# Create 2 users and 3 songs
php artisan concurs:simulate-day --users=2 --songs=3
```

### Admin Test Mode

The simulator now includes an **ADMIN-ONLY Test Mode** that provides additional testing capabilities:

#### **Start Concurs Test** 🚀
- **What it does**: Wipes today's contest data (songs, votes, tiebreaks, winners) and immediately sets the current logged-in admin as the temporary winner
- **Result**: Opens the winner modal UI so the admin can pick tomorrow's theme
- **Bypasses**: Admin can upload multiple songs per day and vote for their own songs during test mode

#### **Declare Real Winner** 🏆
- **What it does**: Computes the REAL winner from current votes using production logic
- **Ignores**: Any temporary admin winner status
- **Result**: Shows the actual winner and opens theme modal if needed

#### **Reset Today** 🧹
- **What it does**: Clears today's contest data but keeps test mode active
- **Use case**: Reset and try different scenarios without losing test mode

#### **Restart Competition** 🔄
- **What it does**: Clears the song list and restarts the competition after theme selection
- **Use case**: After choosing a theme, restart to test the full cycle again
- **Result**: Fresh start with test mode still active

#### **End Test Mode** ❌
- **What it does**: Disables test mode and restores normal contest rules
- **Result**: Admin bypasses are disabled, normal contest logic applies

## ⏰ Simulation Phases

The simulator runs through 5 phases in sequence:

### Phase 1: Submissions Window (09:00 - 20:00)
- Creates test users and songs
- Sets time to 09:00
- Simulates song submissions

### Phase 2: Voting Closes (20:00)
- Sets time to 20:00
- Simulates voting activity
- Closes voting period

### Phase 3: Tiebreak Check & Resolution (20:00 - 20:30)
- Detects ties automatically
- Opens 30-minute tiebreak if needed
- Simulates tiebreak voting
- Resolves tiebreak at 20:30

### Phase 4: Winner Declaration (20:30)
- Declares the winner
- Marks winning song
- Creates winner record

### Phase 5: Theme Selection Deadline (21:00)
- Sets time to 21:00
- Checks if winner chose theme
- Simulates theme selection if needed

## 🧪 Testing

Run the comprehensive test suite:

```bash
php artisan test --filter=ConcursDaySimulatorTest
```

Or run specific tests:

```bash
# Test submissions phase
php artisan test --filter="simulator handles submissions phase correctly"

# Test tiebreak scenario
php artisan test --filter="simulator handles tiebreak scenario correctly"

# Test theme selection
php artisan test --filter="simulator handles theme selection phase correctly"
```

## 🔧 Features

- **Time Simulation**: Uses `Carbon::setTestNow()` to jump through phases
- **Data Seeding**: Creates realistic test data with configurable counts
- **Tie Detection**: Automatically detects and handles voting ties
- **Data Cleanup**: Clears existing contest data before simulation
- **Weekday Validation**: Only runs on weekdays (Mon-Fri)
- **Comprehensive Logging**: Detailed console output for each phase
- **Database Integrity**: Maintains proper relationships between models
- **Admin Test Mode**: Bypasses contest rules for admin users during testing
- **Temporary Winner System**: Allows admin to test theme selection workflow
- **Real Winner Computation**: Production-grade winner calculation during tests

## 📊 Generated Test Data

### Users
- Test users with names like "Test User 1", "Test User 2", etc.
- Verified email addresses
- Secure passwords

### Songs
- Realistic song titles (Amazing Melody, Rock Anthem, etc.)
- YouTube URLs
- Proper user assignments
- Competition date tracking

### Votes
- Random vote counts (1-8 per song)
- Realistic timestamps within submission window
- Proper user and song relationships

### Tiebreaks
- Automatic detection of voting ties
- 30-minute voting windows (20:00-20:30)
- Additional voting simulation during tiebreak

## 🚨 Important Notes

- **Weekend Restriction**: Cannot run on weekends
- **Data Overwrite**: Clears existing contest data for the current day
- **Test Environment**: Designed for testing, not production use
- **Time Manipulation**: Uses Carbon test time for simulation
- **Admin Only**: Test mode bypasses are restricted to admin users only
- **Session Based**: Test mode is stored in session and cache, not database
- **Temporary Winners**: Admin temporary winner status is cleared when declaring real winner

## 🔍 Troubleshooting

### "Cannot simulate on weekends"
- Ensure you're running on a weekday (Monday-Friday)
- The simulator respects the same weekend rules as the real contest

### "No winner could be declared"
- Check that songs and votes were created successfully
- Verify database connections and model relationships

### Test failures
- Ensure database is properly configured for testing
- Check that all required models and migrations exist
- Verify Pest testing framework is installed

### Test Mode Issues
- **Test mode not working**: Ensure user has `is_admin` flag set to true
- **Winner modal not opening**: Check session for `force_theme_modal` and `ap_test_mode`
- **Bypasses not working**: Verify `AdminTestMode` middleware is applied to routes
- **Cache issues**: Clear application cache if test mode markers persist incorrectly

## 📝 Example Output

### Console Simulator Output
```
🎵 Starting Concurs Day Simulator...
=====================================
🧹 Clearing existing data for today...
   ✓ Cleared existing contest data
📝 Phase 1: Submissions Window (09:00 - 20:00)
   ⏰ Time: 09:00
   ✓ Created 3 test users
   ✓ Created 5 test songs
   ✓ Submissions are now open
🗳️  Phase 2: Voting Closes (20:00)
   ⏰ Time: 20:00
   ✓ Simulated voting activity
   ✓ Voting is now closed
⚖️  Phase 3: Tiebreak Check & Resolution (20:00 - 20:30)
   ✅ No tie detected, proceeding to winner declaration
🏆 Phase 4: Winner Declaration
   ⏰ Time: 20:30
   🎉 Winner declared: Rock Anthem
   👤 Winner user: Test User 2
   🗳️  Vote count: 7
🎯 Phase 5: Theme Selection Deadline (21:00)
   ⏰ Time: 21:00
   🎯 Winner has chosen a theme for tomorrow
   📅 Theme set for: 2024-01-16
   ✓ Theme selection phase completed
✅ Concurs day simulation completed successfully!
```

### Admin Test Mode Workflow
```
🎯 TEST MODE: Contest data cleared. You are now the temporary winner - pick a theme!
🏆 TEST MODE: Real winner declared - User #5, Song #12 (8 votes).
🧪 TEST MODE: Winner is User #5 (test mode).
```

### UI Test Mode Indicators
- **TEST MODE ACTIVE** banner when test mode is enabled
- **TEST** badge on theme selection button
- **TEST MODE** label in winner banners
- Organized admin controls under the header

## 🤝 Contributing

When modifying the simulator:

1. Update tests to cover new functionality
2. Maintain the phase-based structure
3. Use descriptive console output
4. Follow existing naming conventions
5. Test edge cases and error conditions

### Test Mode Development
When working on test mode features:

1. **Admin Authentication**: Always verify `auth()->user()->is_admin` before enabling bypasses
2. **Session Management**: Use session and cache for test mode state, not database
3. **UI Indicators**: Add clear visual indicators for test mode status
4. **Bypass Scoping**: Limit rule bypasses to admin users only during test mode
5. **Cleanup**: Ensure test mode can be properly disabled and normal rules restored

### Admin Test Mode Bypasses

When `config('ap.test_mode')` is true and the user is an admin, the following restrictions are bypassed:

#### **Upload Restrictions Bypassed:**
- ✅ **"Already uploaded today"** - Admin can upload unlimited songs per day
- ✅ **"Weekend restriction"** - Admin can upload on weekends
- ✅ **"Time restriction (after 19:30)"** - Admin can upload anytime
- ✅ **"No theme set"** - Admin can upload even without a theme (auto-creates test theme)
- ✅ **"Duplicate song"** - Admin can upload duplicate YouTube URLs

#### **Voting Restrictions Bypassed:**
- ✅ **"Self-vote restriction"** - Admin can vote for their own songs
- ✅ **"Weekend restriction"** - Admin can vote on weekends
- ✅ **"Time restriction (after 20:00)"** - Admin can vote anytime
- ✅ **"Already voted today"** - Admin can vote multiple times per day
- ✅ **"Winner declared"** - Admin can vote even after winner is declared
- ✅ **"Date restriction"** - Admin can vote for songs from any date
- ✅ **"Tiebreak restrictions"** - Admin can vote for any song in tiebreak
- ✅ **"Already voted in tiebreak"** - Admin can vote multiple times in tiebreak

#### **Competition Flow:**
1. **Start Test** → Clears all data, admin becomes temporary winner
2. **Upload Songs** → No restrictions, can upload anytime, anywhere
3. **Vote** → No restrictions, can vote multiple times, for own songs, etc.
4. **Declare Winner** → Computes real winner from current votes
5. **Choose Theme** → Winner picks tomorrow's theme
6. **Restart Competition** → Clears song list, starts fresh cycle
