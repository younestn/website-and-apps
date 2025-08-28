import 'package:flutter/material.dart';
import 'package:sixvalley_vendor_app/common/basewidgets/textfeild/custom_text_feild_widget.dart';
import 'package:sixvalley_vendor_app/localization/language_constrants.dart';
import 'package:sixvalley_vendor_app/features/addProduct/controllers/add_product_controller.dart';
import 'package:sixvalley_vendor_app/utill/dimensions.dart';
import 'package:sixvalley_vendor_app/utill/styles.dart';
class TitleAndDescriptionWidget extends StatefulWidget {
  final AddProductController resProvider;
  final int index;
  const TitleAndDescriptionWidget({super.key, required this.resProvider, required  this.index});

  @override
  State<TitleAndDescriptionWidget> createState() => _TitleAndDescriptionWidgetState();
}

class _TitleAndDescriptionWidgetState extends State<TitleAndDescriptionWidget> {
  @override
  Widget build(BuildContext context) {
    return Padding(padding: const EdgeInsets.symmetric(horizontal : Dimensions.iconSizeSmall),
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 2),
            child: Text('${getTranslated('inset_lang_wise_title_des', context)}',
              style: robotoRegular.copyWith(color: Theme.of(context).hintColor,
                fontSize: Dimensions.fontSizeSmall),),
          ),
          const SizedBox(height: Dimensions.paddingSizeSmall,),


          const SizedBox(height: Dimensions.paddingSizeSmall),
          CustomTextFieldWidget(
            formProduct: true,
            textInputAction: TextInputAction.next,
            controller: TextEditingController(text: widget.resProvider.titleControllerList[widget.index].text),
            textInputType: TextInputType.name,
            required: true,
            hintText: getTranslated('product_name', context),
            border: true,
            borderColor: Theme.of(context).primaryColor.withValues(alpha: .25),
            onChanged: (String text) {
              widget.resProvider.setTitle(widget.index, text);
            },
          ),
          const SizedBox(height: Dimensions.paddingSizeExtraLarge,),


          // Row(
          //   children: [
          //     Text(getTranslated('product_description',context)!,
          //       style: robotoRegular.copyWith(color:  ColorResources.titleColor(context),
          //           fontSize: Dimensions.fontSizeDefault),),
          //
          //     Text('*',style: robotoBold.copyWith(color: ColorResources.mainCardFourColor(context),
          //         fontSize: Dimensions.fontSizeDefault),),
          //   ],
          // ),
          const SizedBox(height: Dimensions.paddingSizeSmall,),


          CustomTextFieldWidget(
            formProduct: true,
            required: true,
            isDescription: true,
            controller: TextEditingController(text: widget.resProvider.descriptionControllerList[widget.index].text),
            onChanged: (String text) => widget.resProvider.setDescription(widget.index, text),
            textInputType: TextInputType.multiline,
            maxLine: 3,
            border: true,
            borderColor: Theme.of(context).primaryColor.withValues(alpha: .25),
            hintText: getTranslated('product_description', context),
          ),

        ],),
    );
  }
}
