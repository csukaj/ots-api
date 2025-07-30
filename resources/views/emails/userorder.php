<html>
<head>
    <style>

    </style>
</head>
<body style="box-sizing : border-box; margin-top : 0; margin-bottom : 0; margin-right : 0; margin-left : 0; font-family : 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size : 14px; line-height : 1.42857143; color : #333333; background-color : #fff; ">
<div class="my-box">
    <h1>Data for new order</h1>
    <table>
        <tr>
            <td>Order ID</td>
            <td><?= $order->id ?></td>
        </tr>
        <tr>
            <td>First Name</td>
            <td><?= $order->first_name ?></td>
        </tr>
        <tr>
            <td>Last Name</td>
            <td><?= $order->last_name ?></td>
        </tr>
        <tr>
            <td>Country</td>
            <td><?= $order->nationality ?></td>
        </tr>
        <tr>
            <td>E-mail address</td>
            <td><?= $order->email ?></td>
        </tr>
        <tr>
            <td>Telephone</td>
            <td><?= $order->telephone ?></td>
        </tr>
        <tr>
            <td>Remarks</td>
            <td><?= $order->remarks ?></td>
        </tr>
        <tr>
            <td>Order time</td>
            <td><?= $order->created_at ?></td>
        </tr>
    </table>

    <h2>Order Details</h2>

    <?php foreach ($order->items as $item) { ?>
        <h3><?= $item->getMode() ?> #<?= $item->order_itemable_index + 1 ?></h3>
        <table>
            <tr>
                <td>Organization</td>
                <td><?= $item->productableModel()->name->en ?></td>
            </tr>
            <tr>
                <td>Device</td>
                <td><?= $item->deviceName ?></td>
            </tr>
            <tr>
                <td>Date</td>
                <td><?= $item->from_date ?> - <?= $item->to_date ?></td>
            </tr>
            <tr>
                <td>Amount</td>
                <td><?= $item->amount ?></td>
            </tr>
            <tr>
                <td>Meal plan</td>
                <td><?= $item->mealPlanName ?></td>
            </tr>
            <tr>
                <td>Guests</td>
                <td>
                    <ul class="list-inline">
                        <?php foreach ($item->guests as $guest) { ?>
                            <li>Guest
                                #<?= ($guest->guest_index + 1) . ':  ' . $guest->first_name . ' ' . $guest->last_name; ?></li>
                        <?php } ?>
                    </ul>
                </td>
            </tr>
            <tr>
                <td>Price</td>
                <td>
                    &euro;<?= $item->price ?>
                    <?php if (!empty($item->compulsoryFee())) {
                        print "+ &euro;" . $item->compulsoryFee() . ' fee';
                    } ?>
                </td>
            </tr>
            <tr>
                <td>Optional</td>
                <td>
                    <ul>
                        <?php if (!empty($item->optionalFees())) {
                            foreach ($item->optionalFees() as $fee) { ?>
                                <li><?php print  $fee->name->en . ': &euro;' . $fee->rack_price; ?></li>
                            <?php }
                        } ?>
                    </ul>
                </td>
            </tr>
        </table>
    <?php } ?>
</div>
</body>
</html>