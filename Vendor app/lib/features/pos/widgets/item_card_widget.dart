import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:sixvalley_vendor_app/features/pos/domain/models/cart_model.dart';
import 'package:sixvalley_vendor_app/helper/price_converter.dart';
import 'package:sixvalley_vendor_app/features/pos/controllers/cart_controller.dart';
import 'package:sixvalley_vendor_app/utill/dimensions.dart';
import 'package:sixvalley_vendor_app/utill/images.dart';
import 'package:sixvalley_vendor_app/utill/styles.dart';
import 'package:sixvalley_vendor_app/common/basewidgets/custom_image_widget.dart';


class ItemCartWidget extends StatelessWidget {
  final CartModel? cartModel;
  final int? index;
  final void Function() onChanged;
  const ItemCartWidget({super.key, this.cartModel, this.index, required this.onChanged});

  @override
  Widget build(BuildContext context) {
    double? price;
    if(cartModel?.variation != null){
      price = cartModel?.variation?.price;
    } else if (cartModel?.varientKey != null) {
      price = cartModel?.digitalVariationPrice;
    } else {
      price = cartModel?.price;
    }


    return Padding(
      padding: const EdgeInsets.only(top: Dimensions.paddingSizeMedium),
      child: Dismissible(
        key: UniqueKey(),
        onDismissed: (DismissDirection direction) {
          Provider.of<CartController>(context, listen: false).removeFromCart(index!);
          onChanged();
        },
        child: Container(decoration: BoxDecoration(
            color: Theme.of(context).cardColor,
              boxShadow: [BoxShadow(color: Theme.of(context).primaryColor.withValues(alpha:.125),
                  spreadRadius: 0.5, blurRadius: 0.3, offset: const Offset(1,2))]),
          padding: const EdgeInsets.fromLTRB( Dimensions.paddingSizeExtraSmall,Dimensions.paddingSizeSmall,0,Dimensions.paddingSizeSmall),
          child: Column(children: [
              Row(children: [

                Expanded(flex: 5,
                  child: Row(crossAxisAlignment: CrossAxisAlignment.center,
                    children: [
                      Container(height: Dimensions.productImageSize,
                          width: Dimensions.productImageSize,
                          padding: const EdgeInsets.all(Dimensions.paddingSizeBorder),
                          child: ClipRRect(
                            borderRadius: BorderRadius.circular(Dimensions.paddingSizeExtraSmall),
                            child: CustomImageWidget(image: '${cartModel!.product!.thumbnailFullUrl?.path}',
                                placeholder: Images.placeholderImage,
                                fit: BoxFit.cover,
                                width: Dimensions.productImageSize,
                                height: Dimensions.productImageSize),
                          ),),
                      const SizedBox(width: Dimensions.paddingSizeSmall),

                      Expanded(child: Text('${cartModel!.product!.name}', maxLines: 1,overflow: TextOverflow.ellipsis,
                        style: robotoRegular.copyWith(fontSize: Dimensions.fontSizeSmall, color: Theme.of(context).textTheme.bodyLarge?.color),)),
                    ],
                  ),
                ),

                Expanded(
                  flex: 4,
                  child: Consumer<CartController>(
                    builder: (context,cartController,_) {
                      return Row(children: [

                        InkWell(
                          onTap: (){
                            cartController.setQuantity(context,false, index, showToaster: true);
                            onChanged();
                          },
                          child: Padding(
                            padding: const EdgeInsets.symmetric(horizontal: Dimensions.paddingSizeExtraSmall),
                            child: Icon(Icons.remove_circle, size: Dimensions.incrementButton,
                                color:cartModel!.quantity!>1? Theme.of(context).colorScheme.onPrimary:Theme.of(context).hintColor),
                          ),
                        ),
                        Center(child: Text(cartModel!.quantity.toString(),
                          style: robotoRegular.copyWith(color: Theme.of(context).textTheme.bodyLarge?.color))),
                        InkWell(
                          onTap: (){
                            cartController.setQuantity(context,true, index, showToaster: true);
                            onChanged();
                          },
                          child: Padding(
                            padding: const EdgeInsets.symmetric(horizontal: Dimensions.paddingSizeExtraSmall),
                            child: Icon(Icons.add_circle, size: Dimensions.incrementButton, color: Theme.of(context).primaryColor),
                          ),
                        ),
                      ],);
                    }
                  ),
                ),

                Expanded(flex: 2,
                  child: Padding(
                    padding: const EdgeInsets.only(right: Dimensions.paddingSizeExtraSmall),
                    child: Text(PriceConverter.convertPrice(context, price!),
                    style: robotoRegular.copyWith(color: Theme.of(context).textTheme.bodyLarge?.color)),
                  )
                ),

              ],),
              const SizedBox(height: Dimensions.paddingSizeExtraSmall),

            ],
          ),
        ),
      ),
    );
  }
}
