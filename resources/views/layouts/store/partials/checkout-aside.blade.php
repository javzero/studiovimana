<aside class="sidebar">
    <div class="padding-top-2x hidden-lg-up"></div>
    <!-- Order Summary Widget-->
    <section class="widget widget-order-summary">
        <h3 class="widget-title">Resumen de Pedido</h3>
        <table class="table">
        <tr>
            <td>Subtotal:</td>
            <td class="text-medium">$ {{ $activeCart['cartTotal'] }}</td>
        </tr>
        <tr>
            <td>Costo de envío:</td>
            <td class="text-medium">
                @if($activeCart['activeCart']->shipping != null && $activeCart['activeCart']->shipping)
                <?php $shippingCost = $activeCart['activeCart']->shipping->price ?>
                    $ {{ $activeCart['activeCart']->shipping->price }}
                @else
                    <?php $shippingCost = '0'; ?>
                    $ 0
                @endif
            </td>
        </tr>
        <tr>
            @if($activeCart['activeCart']->payment !=null && $activeCart['activeCart']->payment->percent > '0')
            <td>Recargo por forma de pago: <br> (% {{ $activeCart['activeCart']->payment->percent }}) </td>
            <td class="text-medium">
                <?php $chargesCost = calcValuePercentNeg($activeCart['cartTotal'], $activeCart['activeCart']->payment->percent) ?>
                $ {{ $chargesCost }}
            </td>    
            @else
            <td>Recargo: </td>
            <td class="text-medium">
                <?php $chargesCost = '0'; ?>
                $ 0
            </td>
            @endif
        </tr>
        <tr>
            <td class="text-lg text-medium"><h4>Total $ {{ $activeCart['cartTotal'] + $shippingCost + $chargesCost  }}</h4></td>
            <input id="CartTotal" type="hidden" name="carttotal" value="{{ $activeCart['cartTotal'] + $shippingCost + $chargesCost }}">
            <td></td>
        </tr>
        </table>
    </section>
    <!-- Featured Products Widget-->  
</aside>