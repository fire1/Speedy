# Speedy EPS Wrapper  (Helper class)
Тази библиотека съдържа обекти които биха помогнали с изчисляването на сума за доставка.

( Библиотеката е в процес на изработка .... )

## Пример за връзка със сървъра

		  $imps = new ConnectionImplements(/* user */, /* pass */);
		  $conn = new ConnectionSpeedyApi($imps);
		  /* @var \EPSFacade $eps */
		  $eps = $conn->getEpsFacade(); // Низът $eps съдържа обекта EPSFacade

## Спомагателния клас

		$shipping =	new ShippingWrapperHelper($eps, $conn);


## Обработка на формата за доставка

		// Този метод обработва входящ масив 
		// от информация подадена HTML форма
		$shipping->setReceiverAddress(/* array */); 

	/* Ключовете които съдържа масива:
     * array['city']        City name
     * array['str_tp']      Street type
     * array['str_nm']      Street name
     * array['str_no']      Street Number
     * array['zip']         Postal code
     * array['flr_no']      Floor Number
     * array['blk_no']      Block Number
     * array['ent_no']      Entrance  Number
     * array['apr_no']      Apartment  Number
     * array['note']        Address Note
     */
     
## Въвеждане на адрес на получател вариант 2
		//
		// With set parameter $arrModel for method,  will reload $arrPost in given format 
		//
		// $arrPost 	- Array from form inputs,selects, textarea & etc ....
		// $arrModel	- Associative Array model for keys. Array keys are acceptable from method.
		// Example: $arrModel =  array('city'=>'city_name','str_tp'=>'street_type','str_nm'=>'street')
		// As folow: 'street_name' is your <input name="street_name" ... 
		$shipping->setReceiverAddress(  $arrPost /* [array from request] ($_POST) */, $arrModel /* array model  */);

## Въвеждане на адрес на получател вариант 3
		//
		//  Този вариант (3) е подобен на 2 с разликата, че информацията се обработва преди метода
		$formData = new ReceiverStreetModel($input, $model); 
     		$shipping->setReceiverAddress($formData);
     		
### Следва още ...
