# Donations for ClassicPress

Donations for ClassicPress is a small plugin that works with [Classic Commerce](https://github.com/ClassicPress-plugins/classic-commerce) to display the donation options in an easy-to-use format on the frontend.

Instead of showing the donation options as a dropdown, this plugin converts them to radio buttons. It also provides website visitors with the option of donating a custom amount.

* * *

## Installation

1. Head over to the [Releases section](https://github.com/timbocode/cp-donations/releases) of this GitHub repository.
2. Expand the "Assets" dropdown by the latest release and download the `cp-donations.zip` file.
3. Go to the "Plugins > Add New" section of your site's dashboard and upload the zip file there using the "Upload Plugin" button.

## Minimum Requirements

- ClassicPress 1.2.0
- Classic Commerce 1.0.3
- PHP 7.2

## Settings

This plugin adds a CP Donations tab in Classic Commerce settings:

**Donations page** - Select the page to be used as the main Donations page where the shortcode (see below) is placed.

**Checkout message (US Only)** - Checkout message shown to visitors from the United States only. This is intended to be a note about donations being tax-deductible.

**Minimum Amount text** - the text to display before the minimum accepted amount.

**Custom Amount text** - the text that appears above the CP Donations input field.

**Add to Cart button text** - the text that appears on the Add to Cart button. Leave blank to inherit the default add to cart text.

**Place Order button text** - the text that appears on the Place Order button on the checkout page. Leave blank to inherit the default text.

## Setting up a donation product

1. Create a variable product.
2. Add product to the Donations category (create category if necessary).
3. Set up attributes for the donation amounts. For example:

`Recurring: $5/month | Recurring: $15/month | Recurring: $30/month | Recurring: $60/month | Recurring: $90/month | Recurring: $custom/month | One-time: $5 | One-time: $15 | One-time: $30 | One-time: $60 | One-time: $90 | One-time: $custom`

4. Select the "`Used for variations option`".
5. On the variations tab, select "`Create variations from all attributes`" from the dropdown.
6. Set the SKU to something beginning with '`recurring_`' or '`once_`', depending on whether this is a recurring or one-off variation.
7. For variations with a custom amount, select the "`Custom amount`" checkbox. This then replaces the "`Regular price`" and "`Sale price`" input boxes with a single "`Minimum amount`" box.
8. Enter a minimum amount for custom amount variations.

The minimum amount can be used to prevent customers making a donation of a value that is less than it costs to process the donation.

## Displaying the donation product on the frontend

This is done by simply adding the following shortcode in a page or post:

```
[cp_donation product_id="xxxx"]
```

where "xxxx" is the ID of the variation product just created.
