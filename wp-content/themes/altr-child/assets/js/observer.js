function onAttributeObserver(node, attribute, callback) {
  const getAttributeValue = () => {
    let attributeValue = '';
    if (attribute === "class") {
      attributeValue = node.classList.toString();
    }

    if (attribute === "style") {
      attributeValue = node.getAttribute("style");
    }
	  
    return attributeValue;
  }


  let lastAttributeValue = getAttributeValue();

  let mutationObserver = new MutationObserver((mutationList) => {
    for (const item of mutationList) {
		if (item.type === 'childList') {
			console.log('han cambiado los hijos');
			break;
		} else if (item.type === 'attributes') {
        	const newAttributeValue = getAttributeValue();
			if (newAttributeValue !== lastAttributeValue) {
			  callback(mutationObserver);
			  lastAttributeValue = newAttributeValue;
			  break;
			}
      }
    }
  });

  mutationObserver.observe(node, { attributes: true });

  return mutationObserver;
}