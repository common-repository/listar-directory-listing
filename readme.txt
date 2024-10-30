=== Listar - Directory Listing & Classifieds WordPress Plugin ===
Contributors: passionui
Website: https://passionui.com
Tags: business directory, car dealer, classified listing, directory listing, event listing, directory listing, hotel listing, booking & payment, mobile api, real estate listing, shopping listing, store locator
Requires at least: 6.4
Requires PHP: 5.8
Tested up to: 6.5.4
Stable tag: 1.0.35
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Listar is Directory Listing & Classifieds for Wordpress sites

== Description ==
Listar is Directory Listing & Classifieds for Wordpress sites

Provides solution for help you organize your listings.

It’s suitable for many kind of mobile directory listing like store locator, tourists or city guide, event & attraction places, property directory.

[Productions](https://listarapp.com) | [Support](https://listarapp.com/support)

Main features:
- Directory listing management
- Booking & payment
- Category management
- Features management
- Location management
- Tag management
- Business hours
- Geo location
- Comment & Rating
- Wishlist
- Color picker
- Icon picker
- Advance filtering: category, feature, location, tag, location, color, price
- Author profile & listing
- Submit directory listing
- Import/Export CSV 
- Claim listing
- Booking: standard, daily, hourly & slot
- Payment: Paypal, Stripe, Cash on Delivery & Bank Transfer
- OTP login
- Firebase push notification
- REST APIs Support

[See more features](https://listarapp.com/pricing/) 

### Author ###
Brought to you by [PassionUI Team](https://listarapp.com)

== Installation ==

= Manual Installation =

 1. Extract the zip file and just drop the contents in the wp-content/plugins/ directory of your WordPress installation

 2. Activate 'Listar Directory Listing' through the 'Plugins' menu in WordPress.

= Better Installation =

 1. Go to Plugins > Add New in your WordPress admin and search for 'Listar Directory Listing'.

 2. Click Install.

== Frequently Asked Questions ==

== Screenshots ==

== Changelog ==
= 1.0.35 =
- Add job queue 
- Improve performance when send email, Firebase notification

= 1.0.34 =
- Fix API can't load file size
- Handle application when can't connect to the server 
- Fix show status based on open hours & add optional setting on the backend
- Migrate from legacy FCM APIs to HTTP v1
- Fix API "Booking Style: Hourly" not working at 00:00

= 1.0.33 =
- Improve security & supports OTP login & OTP email template 
- Fix bug author profile listing 
- Send email notification when user has changed password 
- Only allow feedback when user has claimed or booked the listing
- Check permission submit listing on user profile mobile screen
- Show status base on open hours 
- Settings limit tags, categories list on mobile filtering screen 
- Validate again end time, end date when user booking listing with daily style 
- Add columns author name, email, phone on admin listing page 
- Fix export csv file duplicate phone and fax number 
- Auto focus center map as backend settings when user add new listing on mobile app or web
- Update function check user expire token & allows set token expire time on backend

= 1.0.32 =
- Add option allow deactivate account & clean user's data permanently
- Update claim verify if the listing has claimed or pending to review
- Update workflow claim to pay
= 1.0.31 =
- Fix wishlist widget data 
- API mobile claim listing request 
- API mobile claim listing payment
- Claim listing notification email & Firebase
- Fix bug approval when user submit listing 
= 1.0.30 =
- Fix wrong access profile mobile app
- API login response more first name, last name for auto fill when user make a booking
- Update and reorganize the settings page
- Add author column name on listing admin page
- Send email notification to owner, user when user make a booking 
- Add option modify reset password email content 
- Add option hide admin > Media menu from the left sidebar
- Add API claim listing 
- Export: include social link, galleries image
- Import: support social link, galleries image
- Add validate command check basic setting 
- Add option disable booking on specify listing
- Disable change status listing if administrator enable feature submit listing & approval

= 1.0.29 =
- Support API customize header mobile slider or select option style
- Support API customize select location option
- Fix hide searching with deleted/pending/draft listing
- Fix booking title can't parse
- Fix order banner setting by id 
- Fix import csv listing: support booking price, skip download image if image was existed on system
= 1.0.28 =
- Fix bug can't load default image
- Improve load data when select location on mobile home screen
= 1.0.27 =
- Support import booking price, booking style 
- Add function export all listing with csv format
= 1.0.26 =
- Support API build mobile widget: banner, category, location, listing, Google AdMob, banner image, banner slider
- Support import/export Taxonomy data: categories, locations, features, tags
- Fix bug register account with username is not match special characters
- Add module Blog APIs for mobile app
- Fix bug import CSV can not download image 
= 1.0.25 =
- Support old theme Listar 1.0 is missing setting model
= 1.0.24 =
- Change source code structure with PSR-4 auto loading
- Add customize post type for support add team member
- Fix searching content
= 1.0.23 =
- Fix searching title, content
- Support API search Taxonomy Category/Location
- Add API support accept booking for administrator
= 1.0.22 =
* Add searching address by keyword
* Fix can't save wishlist
* Update image size : thumb, medium, large
* Quick edit booking show list status booking
* Fix listing booking base on author login
* Fix default schedule data format on mobile
* Fix edit listing bug data location format object/array
= 1.0.21 =
* Fix bug Permission Denied although header has sent correct JWT token
* Return booking meta data with first name & last name
* Change push token meta field name
* Add change status booking notification setting
* Add mobile push for booking flow
* Add API filtering request booking & user request booking to author
* Fix social network can store from mobile submit
* Add debug Firebase option: force debug mobile token device
* Add setting booking title format & regular expression
* Calculate rating with approved comment only
* Fix email content enter new line
* Add API return query sub categories
* Change default zoom map from 0 to 10
= 1.0.20 =
* Fix can't save comment & average rate
* Remove html element when show modal error on app if user login fail
* Fix social network can't show on detail screen
= 1.0.19 =
* Fix function check deactivate account
* Fix API can update user photo
= 1.0.18 =
* Add API deactivate account (user can delete their account)
* Support submit social network fields on mobile
= 1.0.17 =
* Fix error can't edit the listing
* Fix Wordpress theme can't see listing data
= 1.0.16 =
* Update API register user with default role as author
* Update Admin Listing only show posting of author
= 1.0.15 =
* Fix init API mobile app missing set image and freeze screen
* Add featured image on admin list: listing, feature, category, location
* Update API return correct image size: thumbnail, full
* Auto set default image if user didn't set featured image
= 1.0.14 =
* Fix bugs can't show galleries when edit
* Add show/hide option social network
* Fix API can't show pending listing data
= 1.0.13 =
* Update setting sections & re-organize setting tab
* Add mobile setting view options: Can show hide field on mobile
* Submit listing & approval
* Fix bug return empty category data
* Add social network link to listing detail
* Support add file attachment on listing
* Support video field on mobile
* Add option show hide function submit listing on mobile app
= 1.0.12 =
* Add more setting section
* Support Wordpress 5.9.2 or higher
* Add booking API feature for the mobile app
* Support WordPress backend review booking, insert/update & delete booking information
* Support 4 booking types: standard, daily, hourly & slot
* Support payment gateway: Paypal, Stripe, Cash on Delivery & Bank Transfer
= 1.0.11 =
* Add setting default featured image
* Improve widget theme
* Fix permission callback REST API policy
= 1.0.10 =
* Add API for mobile app tab Discover
* Add API listing by category and related listing directories by vertical list style
= 1.0.9 =
* User can submit directory listing via mobile app
* User upload photo & change avatar
* Author profile directory listing & comment list
= 1.0.8 =
* Fix call undefined function home screen API
= 1.0.7 =
* Fix comment rating handler
* Add more option for wordpress theme Listar
* Add more theme option function
= 1.0.6 =
* Fix API dashboard error
* Add range time schedule support select time up to 24:00 PM (00:00 AM)
= 1.0.5 =
* Improve function get detail directory, show related data by same category, featured data by random
= 1.0.4 =
* Fix bug can't log user's token device at first time login
= 1.0.3 =
* Import CSV file
* Push notification with FCM
* Fix bugs open hour field can't select up to 23 hours
* Fix bug load low quality image
* Support location widget mobile from Wordpress > Menu
= 1.0.2 =
* Fix bug load image low quality
* Fix bug API first time empty data
* Allow customize dashboard category, popular location from backend
= 1.0.1 =
* Add open hours field for REST APIs view detail place
* Show location column on directory admin list
* Fix comment does not save rating value
* Can’t view by location ID
= 1.0.0 =
* First Release
* Support Listar mobile app

== Upgrade Notice ==
= 1.0.10 =
Upgrade new version support the mobile app Listar FluxPro version 1.1.1
