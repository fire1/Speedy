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
     
### Следва още ...
