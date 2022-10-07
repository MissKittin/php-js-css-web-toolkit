function sleep(ms)
{
	/*
	 * Sleep wrapper
	 *
	 * Usage:
		async function myfunction()
		{
			await sleep(3000);
		}
	 */

	return new Promise(function(resolve){
		setTimeout(resolve, ms)
	});
}